<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $table = 'sales';
    
    protected $fillable = [
        'invoice_no', 'sale_date', 'customer_id', 'subtotal',
        'tax_percentage', 'tax_amount', 'shipping_amount',
        'discount_amount', 'total_amount', 'due_amount', 'ref_no', 'body_date'
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    
    public function details()
    {
        return $this->hasMany(SaleDetail::class, 'sale_id');
    }
    
    public function receivedPayments()
    {
        return $this->hasMany(SalesReceived::class, 'sale_id');
    }
    
    public function oldProductPurchases()
    {
        return $this->hasMany(OldProductPurchase::class, 'sale_id');
    }
    public function partial_payments()
    {
        return $this->hasMany(SalesReceived::class, 'sale_id');
    }
    public function replacement()
    {
        return $this->hasOne(Replacement::class, 'sale_id');
    }
    public function return()
    {
        return $this->hasOne(ReturnModel::class, 'sale_id');
    }
    public function exchange()
    {
        return $this->hasOne(Exchange::class, 'sale_id');
    }
    public function warranty()
    {
        return $this->hasOne(Warranty::class, 'sale_id');
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'sale_id');
    }
    public function saleInvoice()
    {
        return $this->hasOne(SaleInvoice::class, 'sale_id');
    }
    public function saleReturn()
    {
        return $this->hasOne(SaleReturn::class, 'sale_id');
    }
    public function saleExchange()
    {
        return $this->hasOne(SaleExchange::class, 'sale_id');
    }
    public function saleWarranty()
    {
        return $this->hasOne(SaleWarranty::class, 'sale_id');
    }
    public function saleReplacement()
    {
        return $this->hasOne(SaleReplacement::class, 'sale_id');
    }
    public function received_date()
    {
        return $this->hasOne(SalesReceived::class, 'sale_id');
    }
}