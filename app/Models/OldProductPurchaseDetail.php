<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldProductPurchaseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_product_purchase_id',
        'old_product_id',
        'qty',
        'unit',
        'price',
        'total_amount',
        'narration',
    ];

    public function product()
    {
        return $this->belongsTo(OldProduct::class, 'old_product_id');
    }

    public function oldProduct()
    {
        return $this->belongsTo(OldProduct::class, 'old_product_id');
    }

    public function purchase()
    {
        return $this->belongsTo(OldProductPurchase::class, 'old_product_purchase_id');
    }

    public function saleThroughPurchase()
    {
        return $this->hasOneThrough(
            OldProductSale::class,
            OldProductPurchase::class,
            'id',
            'id',
            'old_product_purchase_id',
            'old_product_sale_id'
        );
    }
}