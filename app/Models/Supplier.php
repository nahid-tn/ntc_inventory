<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class supplier extends Model
{
    protected $fillable = [
        'supplier_name',
        'BankName',
        'AccountNumber',
        'mobile',
        'address',
        'status'
    ];

    
    public function purchases()
    {
        return $this->hasMany(Purchase::class,'supplier_id','id');
    }
    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }
    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}