<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ProductionOrder;
use App\Models\Product;
use App\Modules\Payments\DTOs\PaymentDTO;
use App\Modules\Payments\Services\PaymentService;
use App\Modules\Production\DTOs\ProductionOrderDTO;
use App\Modules\Production\Services\ProductionOrderService;
use App\Services\BusinessDaysService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Creación de pedidos vía el bot de WhatsApp/n8n. Reemplaza por completo la
 * implementación anterior (deshabilitada en el hotfix de seguridad del
 * 2026-09-24): esta reusa ProductionOrderService/PaymentService en vez de
 * duplicar su lógica, nunca acepta price/shipping_price del payload
 * (siempre vienen del catálogo), y tiene idempotencia real a nivel de BD.
 *
 * Gate: ability 'whatsapp:orders:create' — el token del bot no tiene
 * ninguna otra ability (no puede leer clientes en bulk, ni pedidos de
 * otros, ni nada financiero agregado).
 */
class WhatsappOrderController extends Controller
{
    public function __construct(
        private ProductionOrderService $productionOrderService,
        private PaymentService $paymentService,
        private BusinessDaysService $businessDays,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'idempotency_key' => 'required|string|max:255',

            'client_first_name' => 'required|string|max:100',
            'client_last_name'  => 'required|string|max:100',
            'client_phone'      => 'required|string|max:20',
            'client_email'      => 'nullable|email|max:255',

            'city_id'           => [
                'required',
                Rule::exists('cities', 'id')->where(
                    fn ($query) => $query->where('department_id', $request->input('department_id'))
                ),
            ],
            'department_id'     => 'required|exists:departments,id',
            'address'           => 'required|string|max:255',

            'product_id'        => [
                'required',
                Rule::exists('products', 'id')->where('active', true),
            ],
            'cart_color'        => [
                'required',
                Rule::exists('cart_colors', 'name')->where('active', true),
            ],

            'sticker'           => 'boolean',
            'sticker_color'     => 'nullable|string|max:100',
            'advance_amount'    => 'nullable|numeric|min:0',
        ], [
            'idempotency_key.required'  => 'idempotency_key es obligatorio.',
            'client_first_name.required'=> 'El nombre del cliente es obligatorio.',
            'client_last_name.required' => 'El apellido del cliente es obligatorio.',
            'client_phone.required'     => 'El teléfono del cliente es obligatorio.',
            'city_id.required'          => 'La ciudad es obligatoria.',
            'city_id.exists'            => 'La ciudad no existe o no pertenece al departamento indicado.',
            'department_id.required'    => 'El departamento es obligatorio.',
            'department_id.exists'      => 'El departamento no existe.',
            'address.required'          => 'La dirección es obligatoria.',
            'product_id.required'       => 'Debes especificar el producto (product_id).',
            'product_id.exists'         => 'El producto no existe o ya no está disponible.',
            'cart_color.required'       => 'El color es obligatorio.',
            'cart_color.exists'         => 'Color no disponible — colores válidos: Gris, Negro.',
        ]);

        // Idempotencia — chequeo rápido antes de intentar nada: cubre el
        // caso normal (reintento de n8n después de un timeout, no una
        // carrera real). La constraint única de la BD es el respaldo para
        // el caso raro de dos llamadas verdaderamente simultáneas.
        $existing = ProductionOrder::where('idempotency_key', $validated['idempotency_key'])->first();
        if ($existing) {
            return $this->respond($existing, 200, true);
        }

        [$client, $conflictNote] = $this->resolveClient($validated);
        $product = Product::findOrFail($validated['product_id']);
        $dueDate = $this->businessDays->calculateDueDate(15)->toDateString();

        try {
            $order = DB::transaction(function () use ($validated, $client, $product, $dueDate, $conflictNote) {
                $order = $this->productionOrderService->create(
                    ProductionOrderDTO::fromRequest([
                        'client_id'       => $client->id,
                        'product_id'      => $product->id,
                        'color'           => $validated['cart_color'],
                        'sticker'         => $validated['sticker'] ?? false,
                        'sticker_color'   => $validated['sticker_color'] ?? null,
                        'observations'    => $conflictNote,
                        'price'           => (float) $product->base_price,
                        'due_date'        => $dueDate,
                        'idempotency_key' => $validated['idempotency_key'],
                        'origin'          => 'whatsapp',
                    ]),
                    auth()->id()
                );

                if (! empty($validated['advance_amount']) && $validated['advance_amount'] > 0) {
                    $this->paymentService->create(
                        PaymentDTO::fromRequest([
                            'production_order_id' => $order->id,
                            'amount'               => $validated['advance_amount'],
                            'type'                 => 'advance',
                            'payment_method'       => 'efectivo',
                            'notes'                => 'Anticipo vía WhatsApp',
                            'paid_at'              => now()->toDateString(),
                        ]),
                        auth()->id()
                    );
                    $order->refreshPaymentBreakdown();
                }

                return $order;
            });
        } catch (UniqueConstraintViolationException $e) {
            // Laravel ya hizo rollback de la transacción antes de que este
            // catch se ejecute — la consulta de abajo corre limpia, fuera
            // de la transacción abortada (necesario en Postgres: una vez
            // que un statement falla, la transacción queda inservible
            // hasta el rollback).
            $existing = ProductionOrder::where('idempotency_key', $validated['idempotency_key'])->first();
            if ($existing) {
                return $this->respond($existing, 200, true);
            }
            throw $e;
        } catch (\Exception $e) {
            // PaymentService::create() lanza \Exception genérica con un
            // código HTTP embebido (ej. 422 si el anticipo supera el saldo)
            // — sin este catch, Laravel la trata como 500 aunque el mensaje
            // ya sea específico y accionable. La transacción ya hizo
            // rollback completo (no queda una orden huérfana sin su pago).
            Log::error($e);

            $hasHttpCode = $e->getCode() >= 400 && $e->getCode() < 600;

            return response()->json([
                'success' => false,
                'message' => $hasHttpCode ? $e->getMessage() : 'Ocurrió un error inesperado al crear el pedido.',
            ], $hasHttpCode ? $e->getCode() : 500);
        }

        return $this->respond($order, 201, false);
    }

    /**
     * Busca el cliente por teléfono y solo rellena los campos que hoy están
     * vacíos — nunca sobrescribe un dato ya presente. Si la dirección
     * recibida difiere de la ya guardada, no se toca el registro del
     * cliente: se devuelve una nota para que quede en las observations del
     * pedido nuevo, y un humano decida qué hacer.
     *
     * @return array{0: Client, 1: ?string}
     */
    private function resolveClient(array $validated): array
    {
        $client = Client::where('phone', $validated['client_phone'])->first();
        $conflictNote = null;

        if ($client) {
            $updates = [];

            if (blank($client->first_name)) {
                $updates['first_name'] = $validated['client_first_name'];
            }
            if (blank($client->last_name)) {
                $updates['last_name'] = $validated['client_last_name'];
            }
            if (blank($client->email) && ! empty($validated['client_email'])) {
                $updates['email'] = $validated['client_email'];
            }
            if (blank($client->city_id)) {
                $updates['city_id'] = $validated['city_id'];
            }
            if (blank($client->department_id)) {
                $updates['department_id'] = $validated['department_id'];
            }

            if (blank($client->address)) {
                $updates['address'] = $validated['address'];
            } elseif (trim($client->address) !== trim($validated['address'])) {
                $conflictNote = "Dirección distinta reportada por WhatsApp: \"{$validated['address']}\". "
                    . "Dirección registrada del cliente sin cambios: \"{$client->address}\".";
            }

            if ($updates) {
                $client->update($updates);
            }

            return [$client, $conflictNote];
        }

        try {
            $client = Client::create([
                'first_name'    => $validated['client_first_name'],
                'last_name'     => $validated['client_last_name'],
                'phone'         => $validated['client_phone'],
                'email'         => $validated['client_email'] ?? null,
                'address'       => $validated['address'],
                'city_id'       => $validated['city_id'],
                'department_id' => $validated['department_id'],
                'active'        => true,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Carrera: otra conversación casi simultánea con el mismo
            // teléfono ya creó el cliente — lo recuperamos en vez de fallar.
            $client = Client::where('phone', $validated['client_phone'])->firstOrFail();
        }

        return [$client, null];
    }

    private function respond(ProductionOrder $order, int $status, bool $isReplay): JsonResponse
    {
        $order->loadMissing(['client.city', 'client.department', 'product', 'currentStage', 'payments']);

        return response()->json([
            'success'           => true,
            'idempotent_replay' => $isReplay,
            'message'           => 'Orden #' . $order->consecutive . ' creada correctamente.',
            'order' => [
                'id'            => $order->id,
                'consecutive'   => str_pad($order->consecutive, 3, '0', STR_PAD_LEFT),

                'client' => [
                    'id'         => $order->client->id,
                    'name'       => $order->client->full_name,
                    'phone'      => $order->client->phone,
                    'address'    => $order->client->address,
                    'city'       => $order->client->city?->name,
                    'department' => $order->client->department?->name,
                ],

                'product'       => $order->product->name,
                'color'         => $order->color,
                'sticker'       => $order->sticker,
                'sticker_color' => $order->sticker_color,
                'observations'  => $order->observations,

                'price'         => (float) $order->price,
                'advance'       => (float) $order->total_paid,
                'balance'       => (float) $order->total_balance,

                'due_date'      => $order->due_date->format('d/m/Y'),
                'due_date_iso'  => $order->due_date->toDateString(),

                'stage'         => $order->currentStage?->name,
                'status'        => $order->status,

                'url'           => url('/production-orders/' . $order->id),
            ],
        ], $status);
    }
}
