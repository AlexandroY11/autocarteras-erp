<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDispatch extends Model
{
    protected $fillable = [
        'production_order_id',
        'guide_number',
        'collected_amount',
        'dispatched_at',
        'dispatched_by',
        'sent_at',
        'sent_by',
        'delivered_at',
        'delivered_by',
        'returned_at',
        'returned_by',
        'return_reason',
    ];

    protected $casts = [
        'collected_amount' => 'decimal:2',
        'dispatched_at'    => 'datetime',
        'sent_at'          => 'datetime',
        'delivered_at'     => 'datetime',
        'returned_at'      => 'datetime',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
