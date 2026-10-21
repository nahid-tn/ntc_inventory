<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_no',
        // 'return_no',
        'return_date',
        'note',
        'total_amount',
        'supplier_id',
        'transport_cost',
        'transport_receive'
    ];
    protected $casts = [
        'return_date' => 'datetime',
    ];
    // public function purchase()
    // {
    //     return $this->belongsTo(Purchase::class);
    // }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
     public function details()
    {
        return $this->hasMany(PurchaseReturnDetail::class);
    }
    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_return_id')
        ->with('product');
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class,'invoice_no','Invoice_no');
    }
    protected $table = 'purchase_returns';

}