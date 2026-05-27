<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_date',
        'description',
        'payment_method',
        'total',
    ];

    public function items()
    {
        return $this->hasMany(ExpenseItem::class);
    }

    protected $casts = [
        'expense_date' => 'date',
    ];
}