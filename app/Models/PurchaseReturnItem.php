<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use HasFactory;

    // ✅ Explicitly tell Laravel the table name
    protected $table = 'purchase_return_items';

    protected $fillable = [
        'purchase_return_id',
        'product_id',
        'qty',
        'unit',
        'price',
        'total',
        'reason',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}