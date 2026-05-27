<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldProductPurchase extends Model
{   
    use HasFactory;
    protected $table = 'old_product_purchases'; // ✅ correct table

    protected $fillable = [
        'sale_id',
        'purchase_no',
        'total',
        'purchase_date',
    ];
    
    public function details()
    {
        return $this->hasMany(
            OldProductPurchaseDetail::class,
            'old_product_purchase_id'
        );
    }

    public function oldProduct()
    {
        return $this->belongsTo(OldProduct::class, 'old_product_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sale_id');
    }
}

    
    