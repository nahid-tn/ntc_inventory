<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_name',
        'qty',
        'unit',
        'price',
        'total'
    ];
    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sale_id');
    }
     public function purchaseDetails()
    {
        return $this->hasMany(OldProductPurchaseDetail::class, 'old_product_id');
    }

    public function lastPurchaseDetail()
    {
        return $this->hasOne(OldProductPurchaseDetail::class, 'old_product_id')
                    ->latest('id'); // অথবা purchase_date
    }
}


   