<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    protected $fillable = [
        'product_name',
        'product_code',
        'model_name',
        'brand_id',
        'category_id',
        'purchase_price',
        'selling_price', // টাইপো ফিক্স
        'description',
        'status',
        'stock',
    ];

    // Laravel 12: টাইপ কাস্টিং - এটা ম্যান্ডেটরি
    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer',
        'status' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function lastPurchaseDetail(): HasOne
    {
        return $this->hasOne(PurchaseDetail::class)->latestOfMany();
    }

    public function purchaseOrderDetails(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    // Laravel 12: নতুন Attribute syntax - accessor লেখার বেস্ট উপায়
    protected function productNameBn(): Attribute
    {
        return Attribute::make(
            get: fn () => app(BanglaHelper::class)->translate($this->product_name)
        );
    }
}