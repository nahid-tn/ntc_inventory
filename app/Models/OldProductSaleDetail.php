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
        'note',
    ];

    protected $casts = [
        'qty'      => 'decimal:2',
        'price'    => 'decimal:2',
        'buy_price'=> 'decimal:2',
        'subtotal' => 'decimal:2',
        'profit'   => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(OldProductSale::class, 'sale_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(OldProduct::class, 'old_product_id', 'id');
    }

    public function oldProduct()
    {
        return $this->belongsTo(OldProduct::class, 'old_product_id', 'id');
    }
}
