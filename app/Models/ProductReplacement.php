<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ProductReplacement extends Model
{
    use HasFactory;

   protected $fillable = [
        'sale_id', 'old_product_id', 'new_product_id', 'qty', 'unit', 'price', 'total', 'body_date', 'replaceMent_date', 'replaceMent_fee', 'is_active'
    ];
    
    // Sale
     public function sale()
        {
            return $this->belongsTo(Sales::class, 'sale_id');
        }

    // New Product
    public function newProduct()
    {
        return $this->belongsTo(Product::class, 'new_product_id');
    }

    // Old Product from product_replacements table
    // যদি old_product_id থাকে
    public function oldProduct()
    {
        return $this->belongsTo(Product::class, 'old_product_id');
    }

    // Old Product from sale_details (optional, manual load)
    public function saleDetail()
    {
        return $this->hasOne(SaleDetail::class, 'product_id', 'old_product_id')
                    ->whereColumn('sale_details.sale_id', 'product_replacements.sale_id');
    }
    public function customer() 
    {
    return $this->sale->customer();
    }
}