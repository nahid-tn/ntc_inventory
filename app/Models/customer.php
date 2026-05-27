<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'mobile',
        'address',
    ];

    // ===================== Relationships =====================
    public function sales()
    {
        return $this->hasMany(Sales::class, 'customer_id');
    }

    public function oldProductSales()
    {
        return $this->hasMany(OldProductSale::class, 'customer_id');
    }
}