<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sales extends Model
{
    protected $table = 'sales';

    protected $fillable = [
        'invoice_no',
        'sale_date',
        'customer_id',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_method',
        'received_type',
        'note',
        'body_date',
        'warranty_period',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sale_date'       => 'date',
            'body_date'       => 'date',
            'subtotal'        => 'decimal:2',
            'tax_percentage'  => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount'    => 'decimal:2',
            'paid_amount'     => 'decimal:2',
            'due_amount'      => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class, 'sale_id');
    }

    public function receivedPayments(): HasMany
    {
        return $this->hasMany(SalesReceived::class, 'sale_id');
    }

    public function partial_payments(): HasMany
    {
        return $this->hasMany(SalesReceived::class, 'sale_id');
    }

    public function oldProductPurchases(): HasMany
    {
        return $this->hasMany(OldProductPurchase::class, 'sale_id');
    }

    public function replacement(): HasOne
    {
        return $this->hasOne(Replacement::class, 'sale_id');
    }

    public function return(): HasOne
    {
        return $this->hasOne(ReturnModel::class, 'sale_id');
    }

    public function exchange(): HasOne
    {
        return $this->hasOne(Exchange::class, 'sale_id');
    }

    public function warranty(): HasOne
    {
        return $this->hasOne(Warranty::class, 'sale_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'sale_id');
    }

    public function saleInvoice(): HasOne
    {
        return $this->hasOne(SaleInvoice::class, 'sale_id');
    }

    public function saleReturn(): HasOne
    {
        return $this->hasOne(SaleReturn::class, 'sale_id');
    }

    public function saleExchange(): HasOne
    {
        return $this->hasOne(SaleExchange::class, 'sale_id');
    }

    public function saleWarranty(): HasOne
    {
        return $this->hasOne(SaleWarranty::class, 'sale_id');
    }

    public function saleReplacement(): HasOne
    {
        return $this->hasOne(SaleReplacement::class, 'sale_id');
    }

    public function received_date(): HasOne
    {
        return $this->hasOne(SalesReceived::class, 'sale_id');
    }
}
