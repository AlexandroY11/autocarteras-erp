<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'consecutive',
        'client_id',
        'product_id',
        'color',
        'sticker',
        'sticker_color',
        'observations',
        'price',
        'advance_payment',
        'due_date',
        'current_stage_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'sticker'         => 'boolean',
        'price'           => 'decimal:2',
        'advance_payment' => 'decimal:2',
        'due_date'        => 'date',
    ];

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

    public function orderStages(): HasMany
    {
        return $this->hasMany(OrderStage::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ─── Helpers ──────────────────────────────────────────

    public function getTotalPaidAttribute(): float
    {
        return $this->payments->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return $this->price - $this->total_paid;
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
        if (in_array($this->status, ['done', 'delivered'])) return 'completed';
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

}