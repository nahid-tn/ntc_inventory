<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReceived extends Model
{
    use HasFactory;

    // Table name (optional, Laravel auto-uses plural of model name)
    protected $table = 'sales_received';

    // Fillable fields
    protected $fillable = [
        'customer_id',
        'sale_id',
        'amount',
        'received_method',
        'received_date',
        'tracking_no',
    ];

    // Dates (so Carbon can be used automatically)
    protected $dates = [
        'received_date',
        'created_at',
        'updated_at',
    ];

    /**
     * Relationship with Sale
     */
    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sale_id');
    }

    /**
     * Relationship with Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}