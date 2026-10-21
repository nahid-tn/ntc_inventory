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
        'product_name_bn',
        'product_code',
        'model_name',
        'brand_id',
        'category_id',
        'purchase_price',
        'wholesale_price',
        'selling_price',
        'description',
        'image',
        'status',
        'is_deleted',
        'stock',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
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

    /**
     * Auto-generate product_code when creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->product_code)) {
                $lastProduct = static::latest('id')->first();
                $nextId      = $lastProduct ? $lastProduct->id + 1 : 1;
                $code        = 'PRD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

                // Duplicate check
                while (static::where('product_code', $code)->exists()) {
                    $nextId++;
                    $code = 'PRD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
                }

                $product->product_code = $code;
            }
        });
    }
}