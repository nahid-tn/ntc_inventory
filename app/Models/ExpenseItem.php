<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseItem extends Model
{
    protected $fillable = [
        'expense_id',
        'item_name',
        'qty',
        'price',
        'subtotal',
        'description',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
