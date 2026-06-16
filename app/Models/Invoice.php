<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'project_id',
        'number',
        'status',
        'date',
        'duedate',
        'prevent_overdue_reminders',
        'allowed_payment_modes',
        'currency',
        'sale_agent',
        'recurring_type',
        'discount_type',
        'admin_note',
        'qty_display_mode',
        'client_note',
        'terms_conditions',
        'subtotal',
        'discount_percent',
        'discount_val',
        'tax',
        'adjustment',
        'total',
        'notes',
        'tags',
        'billing_street',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'shipping_street',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_val' => 'decimal:2',
        'tax' => 'decimal:2',
        'adjustment' => 'decimal:2',
        'total' => 'decimal:2',
        'date' => 'date',
        'duedate' => 'date',
        'prevent_overdue_reminders' => 'boolean',
    ];

    /**
     * Get the client associated with this invoice.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the project this invoice belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the items on this invoice.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the payments registered for this invoice.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
