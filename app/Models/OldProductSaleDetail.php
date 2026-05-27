<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldProductSaleDetail extends Model
{
    protected $table = 'old_product_sale_details';

    protected $fillable = [
        'sale_id',
        'old_product_id',
        'qty',
        'unit',
        'price',
        'buy_price',
        'subtotal',
        'profit',
    ];

    /**
     * Parent Sale Relation
     * old_product_sale_details.sale_id
     * -> old_product_sales.id
     */
    public function sale()
    {
        return $this->belongsTo(
            OldProductSale::class,
            'sale_id',
            'id'
        );
    }

    /**
     * Product Relation (IMPORTANT FIX)
     * old_product_sale_details.old_product_id
     * -> old_products.id
     */
    public function product()
    {
        return $this->belongsTo(
            OldProduct::class,
            'old_product_id',
            'id'
        );
    }

    /**
     * Optional alias relation
     */
    public function oldProduct()
    {
        return $this->belongsTo(
            OldProduct::class,
            'old_product_id',
            'id'
        );
    }
}


/*
====================================================
ALSO CHECK THIS FILE:
app/Models/OldProductSale.php
====================================================

Use this relation:

public function details()
{
    return $this->hasMany(
        OldProductSaleDetail::class,
        'sale_id',
        'id'
    );
}

public function customer()
{
    return $this->belongsTo(
        Customer::class,
        'customer_id',
        'id'
    );
}

====================================================
WHY ERROR HAPPENED
====================================================

Wrong code before:

return $this->belongsTo(
    OldProduct::class,
    'old_product_id',
    'old_product_sale_id'
);

This made Laravel run:

select * from old_products
where old_products.old_product_sale_id in (...)

But old_products table has NO column:
old_product_sale_id

Correct column is:
old_products.id

====================================================
AFTER UPDATE RUN:
====================================================

php artisan optimize:clear
php artisan serve

====================================================
THIS WILL FIX:
SQLSTATE[42S22]: Unknown column
old_products.old_product_sale_id
====================================================
*/
