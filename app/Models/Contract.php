<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject', 'client_id', 'contract_type_id', 'value',
        'start_date', 'end_date', 'status', 'description', 'signed',
    ];

    protected $casts = [
        'value'      => 'float',
        'start_date' => 'date',
        'end_date'   => 'date',
        'signed'     => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
