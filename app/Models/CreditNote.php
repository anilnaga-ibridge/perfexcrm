<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'client_id',
        'amount',
        'date',
        'status',
        'reference',
        'admin_note',
        'terms'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Get the client that belongs to this credit note.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
