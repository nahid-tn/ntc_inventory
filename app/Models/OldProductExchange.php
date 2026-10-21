<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class OldProductExchange extends Model
{
    use HasFactory;

    protected $table = 'old_product_exchanges';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'exchange_no',
        'invoice_no',

        // Customer
        'customer_id',

        // Old Product / Customer Given Product
        'old_product_id',
        'old_product_name',
        'old_qty',
        'old_unit',
        'old_weight_kg',
        'old_price',
        'old_total',

        // Exchange Product / Customer Takes Product
        'exchange_product_id',
        'exchange_product_name',
        'exchange_qty',
        'exchange_unit',
        'exchange_weight_kg',
        'exchange_price',
        'exchange_total',

        // Weight Calculation
        'weight_difference_kg',
        'weight_rate',
        'weight_adjustment',

        // Exchange Calculation
        'exchange_fee',
        'value_difference',
        'settlement_amount',
        'settlement_type',

        // Exchange Information
        'exchange_date',
        'notes',
        'status',

        // Cancellation
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        // Old Product
        'old_qty'              => 'decimal:2',
        'old_weight_kg'        => 'decimal:2',
        'old_price'            => 'decimal:2',
        'old_total'            => 'decimal:2',

        // Exchange Product
        'exchange_qty'         => 'decimal:2',
        'exchange_weight_kg'   => 'decimal:2',
        'exchange_price'       => 'decimal:2',
        'exchange_total'       => 'decimal:2',

        // Weight Calculation
        'weight_difference_kg' => 'decimal:2',
        'weight_rate'          => 'decimal:2',
        'weight_adjustment'    => 'decimal:2',

        // Financial Calculation
        'exchange_fee'         => 'decimal:2',
        'value_difference'     => 'decimal:2',
        'settlement_amount'    => 'decimal:2',

        // Dates
        'exchange_date'        => 'date',
        'cancelled_at'         => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Customer Relationship
    |--------------------------------------------------------------------------
    |
    | old_product_exchanges.customer_id
    |              ↓
    | customers.id
    |
    */

    public function customer()
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Customer Given Product
    |--------------------------------------------------------------------------
    |
    | old_product_exchanges.old_product_id
    |              ↓
    | old_products.id
    |
    */

    public function oldProduct()
    {
        return $this->belongsTo(
            OldProduct::class,
            'old_product_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Customer Takes Product
    |--------------------------------------------------------------------------
    |
    | old_product_exchanges.exchange_product_id
    |              ↓
    | old_products.id
    |
    */

    public function exchangeProduct()
    {
        return $this->belongsTo(
            OldProduct::class,
            'exchange_product_id',
            'id'
        );
    }
}