<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sales;
use App\Models\OldProductSale;
use App\Models\OldProductExchange;

class Customer extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table = 'customers';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'customer_name',
        'mobile',
        'address',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Customer → Sales
     */
    public function sales()
    {
        return $this->hasMany(
            Sales::class,
            'customer_id',
            'id'
        );
    }

    /**
     * Customer → Old Product Sales
     */
    public function oldProductSales()
    {
        return $this->hasMany(
            OldProductSale::class,
            'customer_id',
            'id'
        );
    }

    /**
     * Customer → Old Product Exchanges
     *
     * customers.id
     *      ↓
     * old_product_exchanges.customer_id
     */
    public function oldProductExchanges()
    {
        return $this->hasMany(
            OldProductExchange::class,
            'customer_id',
            'id'
        );
    }
}