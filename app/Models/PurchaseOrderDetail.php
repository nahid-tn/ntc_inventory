<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'qty',
        'unit',
        'price',
        'total',
        'remarks',
    ];

    /*
    =====================================
    Purchase Order Relationship
    =====================================
    */
    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class,
            'purchase_order_id'
        );
    }

    /*
    =====================================
    Product Relationship
    =====================================
    */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}