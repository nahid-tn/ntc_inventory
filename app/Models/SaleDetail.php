<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'qty',
        'unit',
        'price',
        'total',
        'narration',
    ];

    //  ADD THIS
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sale_id');
    }
    public function saleDetail()
    {
        return $this->hasOne(SaleDetail::class, 'sale_id', 'sale_id')
            ->where('product_id', $this->old_product_id); // <-- use value from this model
    }
}
