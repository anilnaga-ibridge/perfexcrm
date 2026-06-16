<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringInvoiceItem extends Model
{
    protected $table = 'recurring_invoice_items';

    protected $fillable = [
        'recurring_invoice_id', 'description', 'long_description',
        'qty', 'unit', 'rate', 'tax_rate', 'order',
    ];

    protected $casts = [
        'qty'      => 'decimal:2',
        'rate'     => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    public function recurringInvoice()
    {
        return $this->belongsTo(RecurringInvoice::class);
    }
}
