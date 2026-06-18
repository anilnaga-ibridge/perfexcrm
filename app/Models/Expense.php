<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'note', 'amount', 'date', 'category_id',
        'client_id', 'reference', 'payment_mode', 'status', 'billable',
    ];

    protected $casts = [
        'amount'   => 'float',
        'date'     => 'date',
        'billable' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }
}
