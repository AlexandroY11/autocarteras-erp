<?php

namespace App\Models;

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
}