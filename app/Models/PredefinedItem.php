<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredefinedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'rate',
        'tax_rate',
        'unit'
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];
}
