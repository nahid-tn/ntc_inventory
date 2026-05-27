<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'Invoice_no',
        // 'reference_no',
        'supplier_id',
        'date',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'shipping_amount',
        'total_amount',
        'payment_method'
    ];
    protected $casts = [
        'date' => 'date',
    ];
    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id','id');
    }
    // Purchase details
    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
    
    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
    // returns
    public function items()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class, 'invoice_no', 'Invoice_no');
    }
    public function supplierPayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'purchase_id');
    }

    public function getDueAttribute()
    {
        return $this->total_amount - $this->payments()->sum('amount');
    }
    
}