<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringInvoice extends Model
{
    use HasFactory;

    protected $table = 'recurring_invoices';

    protected $fillable = [
        'client_id', 'project_id', 'status', 'frequency', 'cycles',
        'cycles_run', 'date_from', 'date_to', 'next_invoice_date',
        'last_sent_at', 'subtotal', 'tax', 'total', 'notes', 'tags',
        // Address overrides
        'billing_street', 'billing_city', 'billing_state', 'billing_zip', 'billing_country',
        'shipping_street', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country',
        // Financial extras
        'discount_type', 'discount_percent', 'discount_val', 'adjustment',
        // Payment & currency
        'currency', 'allowed_payment_modes', 'sale_agent',
        // Notes
        'admin_note', 'client_note', 'terms_conditions',
        // Display
        'qty_display_mode',
    ];

    protected $casts = [
        'subtotal'          => 'decimal:2',
        'tax'               => 'decimal:2',
        'total'             => 'decimal:2',
        'discount_percent'  => 'decimal:2',
        'discount_val'      => 'decimal:2',
        'adjustment'        => 'decimal:2',
        'date_from'         => 'date',
        'date_to'           => 'date',
        'next_invoice_date' => 'date',
        'last_sent_at'      => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function items()
    {
        return $this->hasMany(RecurringInvoiceItem::class)->orderBy('order');
    }
}
