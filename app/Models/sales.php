<?php

namespace App\Models;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $table = 'sales';

    protected $fillable = [
        'invoice_no', 'sale_date', 'customer_id', 'subtotal', 'tax_percentage', 'tax_amount', 'shipping_amount', 'discount_amount', 'total_amount', 'paid_amount', 'payment_method', 'ref_no', 'body_date',];

    protected $casts = [
    'sale_date'     => 'date', 'body_date' => 'date', 'total_amount'=> 'float', 'paid_amount' => 'float', 'due_amount'    => 'float', ];
    public function details()
    {
        return $this->hasMany(SaleDetail::class, 'sale_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function oldProducts()
    {
        return $this->hasMany(OldProduct::class, 'sale_id');
    }

    public function oldProductPurchases()
    {
        return $this->hasMany(OldProductPurchase::class, 'sale_id');
    }
    public function receivedPayments()
    {
        return $this->hasMany(SalesReceived::class, 'sale_id');
    }
    public function getTotalPaidAttribute()
    {
        return ($this->paid_amount ?? 0)
            + $this->receivedPayments()->sum('amount');
    }
    public function getDueAmountAttribute()
    {
        return $this->total_amount - $this->total_paid;
    }
    
}