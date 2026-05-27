<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class oldSaleDetail extends Model
{
    protected $fillable = [
        'sale_id',
        'product_name',
        'qty',
        'unit',
        'price',
        'subtotal',
        'stock'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
}

