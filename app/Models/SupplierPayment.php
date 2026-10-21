<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id', 'purchase_id', 'amount', 'payment_date', 'BankName', 'account_number', 'payment_method', 'cheque_number', 'transaction_number', 'cheque_image', 'deposit_slip', 'note'
    ];

    protected $casts = [
    'payment_date' => 'datetime:Y-m-d',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    protected $table = 'supplier_payments';
    public function paymentDetails()
    {
        return $this->hasManyThrough(
            PurchaseDetail::class,
            Purchase::class,
            'id', // purchases.id
            'purchase_id', // purchase_details.purchase_id
            'purchase_id', // supplier_payments.purchase_id
            'id'
        );
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
    
    protected $appends = ['due_after'];
    public function getDueAfterAttribute()
    {
        if (!$this->purchase) {
            return 0;
        }

        $paidSoFar = $this->purchase->payments
            ->where('payment_date', '<=', $this->payment_date)
            ->sum('amount');

        return $this->purchase->total_amount - $paidSoFar;
    }
}