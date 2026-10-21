<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    protected $table = 'sale_details';

    protected $fillable = [
        'sale_id',
        'product_id',
        'qty',
        'unit',
        'price',
        'total',
        'narration',
    ];

    protected function casts(): array
    {
        return [
            'qty'   => 'decimal:2',
            'price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sale_id');
    }
}
