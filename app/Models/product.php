<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    protected $fillable = [
        'product_name',
        'product_code',
        'model_name',
        'brand_id',
        'category_id',
        'purchase_price',
        'salling_price',
        'description',
        'status',
        'stock',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function getProductNameBnAttribute()
    {
        return $this->enToBn($this->product_name);
    }
        private function enToBn($text)
    {
        $map = [
            'a'=>'আ','b'=>'ব','c'=>'ক','d'=>'দ','e'=>'এ','f'=>'ফ','g'=>'গ',
            'h'=>'হ','i'=>'ই','j'=>'জ','k'=>'ক','l'=>'ল','m'=>'ম','n'=>'ন',
            'o'=>'ও','p'=>'প','q'=>'ক','r'=>'র','s'=>'স','t'=>'ত','u'=>'উ',
            'v'=>'ভ','w'=>'ও','x'=>'ক্স','y'=>'য়','z'=>'জ'
        ];

        return strtr(strtolower($text ?? ''), $map);
    }
    public function lastPurchaseDetail()
    {
        // Latest purchase detail per product
        return $this->hasOne(PurchaseDetail::class)->latestOfMany();
    }
    public function purchaseOrderDetails()
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }
}