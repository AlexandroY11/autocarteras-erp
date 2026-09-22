<?php

namespace App\Models;

use Carbon\Carbon;
use App\Services\PaymentAllocationService;
use App\ValueObjects\PaymentBreakdown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Único mapeo enum→español para estos dos campos — cualquier vista o
     * reporte que necesite mostrarlos legibles debe usar status_label /
     * dispatch_status_label, no traducir por su cuenta.
     */
    public const STATUS_LABELS = [
        'pending' => 'Pendiente',
        'in_progress' => 'En proceso',
        'done' => 'Terminado',
        'cancelled' => 'Cancelado',
    ];

    public const DISPATCH_STATUS_LABELS = [
        'pending_dispatch' => 'Pendiente de despacho',
        'dispatched' => 'Despachado',
        'sent' => 'En tránsito',
        'delivered' => 'Entregado',
        'returned' => 'Devuelto',
    ];

    protected $fillable = [
        'consecutive',
        'client_id',
        'product_id',
        'color',
        'sticker',
        'sticker_color',
        'observations',
        'price',
        'shipping_price',
        'due_date',
        'current_stage_id',
        'status',
        'dispatch_status',
        'created_by',
    ];

    protected $casts = [
        'sticker'         => 'boolean',
        'price'           => 'decimal:2',
        'shipping_price'  => 'decimal:2',
        'due_date'        => 'date',
    ];

    protected ?PaymentBreakdown $paymentBreakdownCache = null;

    // ─── Relaciones ───────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Siguiente etapa activa después de la actual (la misma consulta que ya
     * usa ProductionOrderService::advanceStage() para decidir a dónde avanza
     * la orden) — única fuente de esto, para no duplicarla en la vista.
     */
    public function nextStage(): ?Stage
    {
        return Stage::where('active', true)
            ->where('order', '>', optional($this->currentStage)->order ?? 0)
            ->orderBy('order')
            ->first();
    }

    public function orderStages(): HasMany
    {
        return $this->hasMany(OrderStage::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function orderDispatches(): HasMany
    {
        return $this->hasMany(OrderDispatch::class);
    }

    /**
     * El intento de despacho actualmente abierto (el más reciente sin
     * completed_at equivalente — sin devolución ni entrega registrada aún).
     */
    public function currentDispatch(): ?OrderDispatch
    {
        return $this->orderDispatches()
            ->whereNull('delivered_at')
            ->whereNull('returned_at')
            ->latest('dispatched_at')
            ->first();
    }

    /**
     * El despacho más reciente sin importar su estado (a diferencia de
     * currentDispatch(), que excluye delivered/returned) — usa la relación
     * ya cargada en memoria si está disponible para no generar N+1 al
     * listar varias órdenes.
     */
    public function latestDispatch(): ?OrderDispatch
    {
        return $this->relationLoaded('orderDispatches')
            ? $this->orderDispatches->sortByDesc('dispatched_at')->first()
            : $this->orderDispatches()->latest('dispatched_at')->first();
    }

    /**
     * true si la orden ya salió de pending_dispatch pero su despacho más
     * reciente todavía no tiene número de guía capturado. Es solo un
     * recordatorio visual — nunca bloquea el avance del pedido.
     */
    public function getGuideNumberMissingAttribute(): bool
    {
        if (in_array($this->dispatch_status, [null, 'pending_dispatch'], true)) {
            return false;
        }

        return blank(optional($this->latestDispatch())->guide_number);
    }

    /**
     * Momento en que terminó producción: completed_at de la última etapa
     * completada (la que cerró el ciclo y dejó status=done). Null si la
     * orden todavía no ha completado ninguna etapa.
     */
    public function getProductionCompletedAtAttribute(): ?Carbon
    {
        $stage = $this->relationLoaded('orderStages')
            ? $this->orderStages->whereNotNull('completed_at')->sortByDesc('completed_at')->first()
            : $this->orderStages()->whereNotNull('completed_at')->latest('completed_at')->first();

        return $stage?->completed_at;
    }

    // ─── Helpers ──────────────────────────────────────────

    /**
     * Desglose de pagos (envío primero, excedente a producto). Se calcula una
     * sola vez por instancia — App\Services\PaymentAllocationService es la
     * única fuente de esta lógica, no se repite en controllers/services.
     */
    public function paymentBreakdown(): PaymentBreakdown
    {
        return $this->paymentBreakdownCache ??= app(PaymentAllocationService::class)->breakdown($this);
    }

    /**
     * Invalida el desglose cacheado — llamar después de crear/borrar un pago
     * de esta misma instancia para forzar un recálculo con los datos actuales.
     */
    public function refreshPaymentBreakdown(): void
    {
        $this->paymentBreakdownCache = null;
        $this->unsetRelation('payments');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->paymentBreakdown()->totalPaid;
    }

    public function getShippingPaidAttribute(): float
    {
        return $this->paymentBreakdown()->shippingPaid;
    }

    public function getShippingBalanceAttribute(): float
    {
        return $this->paymentBreakdown()->shippingBalance;
    }

    public function getProductPaidAttribute(): float
    {
        return $this->paymentBreakdown()->productPaid;
    }

    public function getProductBalanceAttribute(): float
    {
        return $this->paymentBreakdown()->productBalance;
    }

    public function getTotalBalanceAttribute(): float
    {
        return $this->paymentBreakdown()->totalBalance;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getDispatchStatusLabelAttribute(): ?string
    {
        if ($this->dispatch_status === null) {
            return null;
        }

        return self::DISPATCH_STATUS_LABELS[$this->dispatch_status] ?? $this->dispatch_status;
    }

    /**
     * "Vencido"/"Por vencer"/"A tiempo" — colapsa critical+warning de
     * time_status en un solo "Por vencer" para mostrar/reportar. Null para
     * órdenes ya terminadas/canceladas, donde el seguimiento de fecha
     * límite deja de tener sentido. Única fuente — usado tanto en las
     * tarjetas (con su color) como en los reportes (HasDueDateTracking).
     */
    public function getTimeTrackingLabelAttribute(): ?string
    {
        if (in_array($this->status, ['done', 'cancelled'], true)) {
            return null;
        }

        return match ($this->time_status) {
            'overdue' => 'Vencido',
            'critical', 'warning' => 'Por vencer',
            'on_time' => 'A tiempo',
            default => null,
        };
    }

    /**
     * Mismo criterio de color ya usado en orders/index.blade.php para
     * time_status, colapsado a los 3 estados de time_tracking_label.
     */
    public function getTimeTrackingColorClassAttribute(): string
    {
        return match ($this->time_tracking_label) {
            'Vencido' => 'bg-red-100 text-red-700 border border-red-200',
            'Por vencer' => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
            'A tiempo' => 'bg-green-100 text-green-700 border border-green-200',
            default => 'bg-gray-50 text-gray-400',
        };
    }

    /**
     * Días hábiles restantes hasta due_date (BusinessDaysService — única
     * fuente de días hábiles del sistema). Positivo si aún no vence,
     * negativo si ya está vencido. Null para done/cancelled o sin due_date.
     */
    public function getBusinessDaysRemainingAttribute(): ?int
    {
        if (in_array($this->status, ['done', 'cancelled'], true) || ! $this->due_date) {
            return null;
        }

        return app(\App\Services\BusinessDaysService::class)->businessDaysUntil($this->due_date);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        // Usamos startOfDay() para comparar fechas sin importar la hora
        return Carbon::now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false);
    }

    /**
     * Calcula la holgura (margen de días).
     */
    public function getSlackAttribute(): ?int
    {
        if (!$this->due_date || !$this->product) {
            return null;
        }
        
        $daysRemaining = $this->days_remaining;
        $daysNeeded = $this->product->avg_production_days;

        return $daysRemaining - $daysNeeded;
    }

    /**
     * Determina el estado de tiempo de la orden.
     */
    public function getTimeStatusAttribute(): string
    {
        if ($this->status === 'done') return 'completed';
        if (!$this->due_date) return 'on_time';
        
        $today = now()->startOfDay();
        $dueDate = $this->due_date->startOfDay();
        
        if ($dueDate->isPast() && !$dueDate->isToday()) return 'overdue';

        $daysRemaining = $today->diffInDays($dueDate, false);
        $avgDays = $this->product->avg_production_days ?? 0;
        $slack = $daysRemaining - $avgDays;

        if ($slack < 0) return 'critical';
        if ($slack <= 2) return 'warning';
        
        return 'on_time';
    }

    /**
     * Filtro de BD equivalente a getTimeStatusAttribute() para 'overdue' y
     * 'critical' — time_status es un accessor en PHP, no una columna, así
     * que no se puede hacer where('time_status', ...) directo. Extraído
     * aquí como fuente única: antes vivía duplicado (y funcional) en
     * ProductionOrderController::index(), una ruta huérfana que renderiza
     * la misma vista pero a la que ya no llega ningún enlace del panel —
     * la ruta real (OrderController::index(), /orders) nunca lo tenía.
     * Si getTimeStatusAttribute() cambia su criterio de 'overdue'/'critical',
     * este scope debe actualizarse igual para no desincronizarse.
     */
    public function scopeTimeStatus(Builder $query, ?string $status): Builder
    {
        if ($status === 'overdue') {
            return $query->where('due_date', '<', now()->startOfDay());
        }

        if ($status === 'critical') {
            // (due_date - CURRENT_DATE) es el equivalente nativo de Postgres
            // a DATEDIFF(due_date, NOW()) — diferencia en días completos.
            return $query->whereHas('product', function ($q) {
                $q->whereRaw('(production_orders.due_date - CURRENT_DATE) < products.avg_production_days');
            })->where('due_date', '>=', now()->startOfDay());
        }

        return $query;
    }

    /**
     * Búsqueda por # de orden, nombre/apellido del cliente, o nombre del
     * producto. Todo el OR va agrupado dentro de su propio where(function
     * ...) a propósito — sin ese grupo, un ->orWhere('consecutive', ...)
     * encadenado directo sobre la query rompe cualquier otra condición ya
     * puesta (AND liga más fuerte que OR en SQL), dejando que el resultado
     * se salte filtros obligatorios (ej. status != cancelled, o el filtro
     * de habilidad de Worker/Director) con solo que el término buscado
     * matchee. Verificado que esto pasaba de verdad en el código anterior
     * antes de extraer este scope — no es solo una precaución teórica.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('consecutive', 'like', "%{$term}%")
                ->orWhereHas('client', fn ($q) => $q->where('first_name', 'ilike', "%{$term}%")
                    ->orWhere('last_name', 'ilike', "%{$term}%")
                )
                ->orWhereHas('product', fn ($q) => $q->where('name', 'ilike', "%{$term}%"));
        });
    }
}
