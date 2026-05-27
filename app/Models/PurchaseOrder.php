<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_date',
        'subtotal',
        'discount',
        'shipping',
        'tax',
        'total_amount',
        'status',
        'payment_method',
        'note',
    ];

    /*
    =====================================
    Supplier Relationship
    =====================================
    */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /*
    =====================================
    Purchase Order Details
    =====================================
    */
    public function details()
    {
        return $this->hasMany(
            PurchaseOrderDetail::class,
            'purchase_order_id'
        );
    }
}