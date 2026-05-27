<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldProductSale extends Model
{
    protected $casts = [
        'sale_date' => 'date',
        'subtotal'  => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'due_amount'   => 'decimal:2',
    ];
    protected $fillable = [
        'invoice_no',
        'customer_id',
        'sale_date',
        'subtotal',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_method',
    ];
    public function details()
    {
        return $this->hasMany(OldProductSaleDetail::class, 'sale_id');
    }
    public function products()
    {
        return $this->hasMany(OldProduct::class, 'sale_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function oldProduct() {
    return $this->belongsTo(OldProduct::class, 'old_product_id');
    }
}