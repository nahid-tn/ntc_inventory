<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReceived extends Model
{
    use HasFactory;

    protected $table = 'sales_received';

    protected $fillable = [
        'customer_id',
        'sale_id',
        'amount',
        'received_method',
        'received_date',
        'tracking_no',
        'payment_type',
    ];

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'amount'        => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sale_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(
            PaymentAllocation::class,
            'sales_received_id'
        );
    }
}
