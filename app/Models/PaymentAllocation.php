<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $table = 'payment_allocations';

    protected $fillable = [
        'sales_received_id',
        'sale_id',
        'amount',
    ];

    protected $casts = [
        'sales_received_id' => 'integer',
        'sale_id' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(
            SalesReceived::class,
            'sales_received_id'
        );
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(
            Sales::class,
            'sale_id'
        );
    }
}
