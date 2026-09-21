<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\City;
use App\Models\Department;
use App\Models\Payment;
use App\Models\Product;
use App\Modules\Production\DTOs\ProductionOrderDTO;
use App\Modules\Production\Services\ProductionOrderService;
use App\Services\BusinessDaysService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsappOrderController extends Controller
{
    public function __construct(
        private ProductionOrderService $service,
        private BusinessDaysService    $businessDays,
    ) {}

    // Borrador fase 2 — creación de órdenes vía bot de WhatsApp/n8n. No eliminar.
    // public function store(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'client_id'         => 'nullable|exists:clients,id',
    //         'client_phone'      => 'required|string|max:20',

    //         // Si no viene client_id, TODOS estos son obligatorios
    //         'client_first_name' => 'required_without:client_id|string|max:100',
    //         'client_last_name'  => 'required_without:client_id|string|max:100',
    //         'client_address'    => 'required_without:client_id|string|max:255',
    //         'client_department' => 'required_without:client_id|string|max:100',
    //         'client_city'       => 'required_without:client_id|string|max:100',

    //         'product_id'        => 'required|exists:products,id',
    //         'color'             => 'required|in:Negro,Gris,Beige',
    //         'sticker'           => 'boolean',
    //         'sticker_color'     => 'required_if:sticker,true|nullable|string|max:100',
    //         'observations'      => 'nullable|string',
    //         'price'             => 'nullable|numeric|min:0',
    //         'advance_payment'   => 'nullable|numeric|min:0',
    //         'due_date'          => 'nullable|date',
    //     ], [
    //         'client_phone.required'              => 'El teléfono del cliente es obligatorio.',
    //         'client_first_name.required_without' => 'El nombre del cliente es obligatorio para clientes nuevos.',
    //         'client_last_name.required_without'  => 'El apellido del cliente es obligatorio para clientes nuevos.',
    //         'client_address.required_without'    => 'La dirección del cliente es obligatoria para clientes nuevos.',
    //         'client_department.required_without' => 'El departamento del cliente es obligatorio para clientes nuevos.',
    //         'client_city.required_without'       => 'La ciudad del cliente es obligatoria para clientes nuevos.',
    //         'product_id.required'                => 'Debes especificar el producto (product_id).',
    //         'product_id.exists'                  => 'El producto especificado no existe en el sistema.',
    //         'client_id.exists'                   => 'El cliente especificado no existe en el sistema.',
    //         'color.required'                     => 'El color de la cartera es obligatorio.',
    //         'color.in'                           => 'El color debe ser exactamente: Negro, Gris o Beige.',
    //         'sticker_color.required_if'          => 'Si lleva calcomanía, debes especificar el color de la calcomanía.',
    //         'price.numeric'                      => 'El precio debe ser un número.',
    //         'advance_payment.numeric'            => 'El anticipo debe ser un número.',
    //         'due_date.date'                      => 'La fecha debe tener formato YYYY-MM-DD.',
    //     ]);

    //     // ── Resolver cliente ──────────────────────────────────
    //     if ($request->filled('client_id')) {
    //         $client = Client::findOrFail($request->client_id);
    //     } else {
    //         // Buscar por teléfono primero
    //         $client = Client::where('phone', $request->client_phone)->first();

    //         if (!$client) {
    //             $departmentId = null;
    //             $cityId       = null;

    //             if ($request->filled('client_department')) {
    //                 $dept = Department::where('name', 'ilike', '%' . $request->client_department . '%')
    //                     ->first();
    //                 if ($dept) $departmentId = $dept->id;
    //             }

    //             if ($request->filled('client_city') && $departmentId) {
    //                 $city = City::where('department_id', $departmentId)
    //                     ->where('name', 'ilike', '%' . $request->client_city . '%')
    //                     ->first();
    //                 if ($city) $cityId = $city->id;
    //             }

    //             $client = Client::create([
    //                 'first_name'    => $request->client_first_name,
    //                 'last_name'     => $request->client_last_name,
    //                 'phone'         => $request->client_phone,
    //                 'address'       => $request->client_address,
    //                 'department_id' => $departmentId,
    //                 'city_id'       => $cityId,
    //                 'active'        => true,
    //             ]);
    //         }
    //     }

    //     // ── Resolver producto y precio ────────────────────────
    //     $product = Product::findOrFail($request->product_id);
    //     $price   = $request->price ?? $product->base_price;

    //     // ── Calcular fecha compromiso ─────────────────────────
    //     $dueDate = $request->filled('due_date')
    //         ? $request->due_date
    //         : $this->businessDays->calculateDueDate(15)->toDateString();

    //     // ── Crear orden ───────────────────────────────────────
    //     $order = $this->service->create(
    //         ProductionOrderDTO::fromRequest([
    //             'client_id'       => $client->id,
    //             'product_id'      => $product->id,
    //             'color'           => $request->color,
    //             'sticker'         => $request->boolean('sticker', false),
    //             'sticker_color'   => $request->sticker_color,
    //             'observations'    => $request->observations,
    //             'price'           => $price,
    //             'advance_payment' => $request->advance_payment ?? 0,
    //             'due_date'        => $dueDate,
    //         ]),
    //         auth()->id()
    //     );

    //     // ── Registrar anticipo ────────────────────────────────
    //     if ($request->filled('advance_payment') && $request->advance_payment > 0) {
    //         Payment::create([
    //             'production_order_id' => $order->id,
    //             'amount'              => $request->advance_payment,
    //             'type'                => 'advance',
    //             'payment_method'      => 'efectivo',
    //             'notes'               => 'Anticipo vía WhatsApp',
    //             'paid_at'             => now()->toDateString(),
    //             'registered_by'       => auth()->id(),
    //         ]);
    //     }

    //     $order->load(['client.city', 'client.department', 'product', 'currentStage', 'payments']);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Orden #' . $order->consecutive . ' creada correctamente.',
    //         'order'   => [
    //             'id'            => $order->id,
    //             'consecutive'   => str_pad($order->consecutive, 3, '0', STR_PAD_LEFT),
    //             'client'        => [
    //                 'id'         => $client->id,
    //                 'name'       => $client->full_name,
    //                 'phone'      => $client->phone,
    //                 'address'    => $client->address,
    //                 'city'       => $client->city?->name,
    //                 'department' => $client->department?->name,
    //                 'is_new'     => $client->wasRecentlyCreated,
    //             ],
    //             'product'       => $product->name,
    //             'color'         => $order->color,
    //             'sticker'       => $order->sticker,
    //             'sticker_color' => $order->sticker_color,
    //             'observations'  => $order->observations,
    //             'price'         => (float) $order->price,
    //             'advance'       => (float) $order->payments->sum('amount'),
    //             'balance'       => (float) ($order->price - $order->payments->sum('amount')),
    //             'due_date'      => $order->due_date->format('d/m/Y'),
    //             'due_date_iso'  => $order->due_date->toDateString(),
    //             'stage'         => $order->currentStage?->name,
    //             'status'        => $order->status,
    //             'url'           => url('/production-orders/' . $order->id),
    //         ],
    //     ], 201);
    // }

    public function store(Request $request): JsonResponse
    {
        // ======================================
        // Validación inicial
        // ======================================
        $request->validate([
            'client_phone'      => 'required|string|max:20',

            'product_id'        => 'required|exists:products,id',

            'color'             => 'required|in:Negro,Gris,Beige',

            'sticker'           => 'boolean',
            'sticker_color'     => 'required_if:sticker,true|nullable|string|max:100',

            'observations'      => 'nullable|string',
            'price'             => 'nullable|numeric|min:0',
            'advance_payment'   => 'nullable|numeric|min:0',
            'due_date'          => 'nullable|date',
        ], [
            'client_phone.required' => 'El teléfono del cliente es obligatorio.',
            'product_id.required'   => 'Debes especificar el producto (product_id).',
            'product_id.exists'     => 'El producto especificado no existe.',
            'color.required'        => 'El color de la cartera es obligatorio.',
            'color.in'              => 'El color debe ser exactamente: Negro, Gris o Beige.',
            'sticker_color.required_if' => 'Si lleva calcomanía, debes especificar el color de la calcomanía.',
            'price.numeric'         => 'El precio debe ser un número.',
            'advance_payment.numeric' => 'El anticipo debe ser un número.',
            'due_date.date'         => 'La fecha debe tener formato YYYY-MM-DD.',
        ]);

        // ======================================
        // Resolver cliente
        // ======================================
        $client = Client::where('phone', $request->client_phone)->first();

        if (!$client) {

            $request->validate([
                'client_first_name' => 'required|string|max:100',
                'client_last_name'  => 'required|string|max:100',
                'client_address'    => 'required|string|max:255',
                'client_department' => 'required|string|max:100',
                'client_city'       => 'required|string|max:100',
            ], [
                'client_first_name.required' => 'El nombre del cliente es obligatorio para clientes nuevos.',
                'client_last_name.required'  => 'El apellido del cliente es obligatorio para clientes nuevos.',
                'client_address.required'    => 'La dirección del cliente es obligatoria para clientes nuevos.',
                'client_department.required' => 'El departamento del cliente es obligatorio para clientes nuevos.',
                'client_city.required'       => 'La ciudad del cliente es obligatoria para clientes nuevos.',
            ]);

            $departmentId = null;
            $cityId = null;

            $department = Department::where('name', 'ilike', '%' . $request->client_department . '%')
                ->first();

            if ($department) {
                $departmentId = $department->id;
            }

            if ($departmentId) {

                $city = City::where('department_id', $departmentId)
                    ->where('name', 'ilike', '%' . $request->client_city . '%')
                    ->first();

                if ($city) {
                    $cityId = $city->id;
                }
            }

            $client = Client::create([
                'first_name'    => $request->client_first_name,
                'last_name'     => $request->client_last_name,
                'phone'         => $request->client_phone,
                'address'       => $request->client_address,
                'department_id' => $departmentId,
                'city_id'       => $cityId,
                'active'        => true,
            ]);
        }

        // ======================================
        // Resolver producto
        // ======================================
        $product = Product::findOrFail($request->product_id);

        // ======================================
        // Resolver precio
        // ======================================
        $price = $request->price ?? $product->base_price;

        // ======================================
        // Calcular fecha compromiso
        // ======================================
        $dueDate = $request->filled('due_date')
            ? $request->due_date
            : $this->businessDays->calculateDueDate(15)->toDateString();

        // ======================================
        // Crear orden
        // ======================================
        $order = $this->service->create(
            ProductionOrderDTO::fromRequest([
                'client_id'       => $client->id,
                'product_id'      => $product->id,
                'color'           => $request->color,
                'sticker'         => $request->boolean('sticker', false),
                'sticker_color'   => $request->sticker_color,
                'observations'    => $request->observations,
                'price'           => $price,
                'due_date'        => $dueDate,
            ]),
            auth()->id()
        );

        // ======================================
        // Registrar anticipo
        // ======================================
        if ($request->filled('advance_payment') && $request->advance_payment > 0) {

            Payment::create([
                'production_order_id' => $order->id,
                'amount'              => $request->advance_payment,
                'type'                => 'advance',
                'payment_method'      => 'efectivo',
                'notes'               => 'Anticipo vía WhatsApp',
                'paid_at'             => now()->toDateString(),
                'registered_by'       => auth()->id(),
            ]);
        }

        // ======================================
        // Cargar relaciones
        // ======================================
        $order->load([
            'client.city',
            'client.department',
            'product',
            'currentStage',
            'payments',
        ]);

        // ======================================
        // Respuesta
        // ======================================
        return response()->json([
            'success' => true,
            'message' => 'Orden #' . $order->consecutive . ' creada correctamente.',
            'order' => [
                'id'            => $order->id,
                'consecutive'   => str_pad($order->consecutive, 3, '0', STR_PAD_LEFT),

                'client' => [
                    'id'         => $client->id,
                    'name'       => $client->full_name,
                    'phone'      => $client->phone,
                    'address'    => $client->address,
                    'city'       => $client->city?->name,
                    'department' => $client->department?->name,
                    'is_new'     => $client->wasRecentlyCreated,
                ],

                'product'       => $product->name,
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
        ], 201);
    }
}
