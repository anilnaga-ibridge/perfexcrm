<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_invoices', function (Blueprint $table) {
            // Address overrides
            $table->text('billing_street')->nullable()->after('tags');
            $table->string('billing_city')->nullable()->after('billing_street');
            $table->string('billing_state')->nullable()->after('billing_city');
            $table->string('billing_zip')->nullable()->after('billing_state');
            $table->string('billing_country')->nullable()->after('billing_zip');
            $table->text('shipping_street')->nullable()->after('billing_country');
            $table->string('shipping_city')->nullable()->after('shipping_street');
            $table->string('shipping_state')->nullable()->after('shipping_city');
            $table->string('shipping_zip')->nullable()->after('shipping_state');
            $table->string('shipping_country')->nullable()->after('shipping_zip');

            // Financial extras
            $table->string('discount_type')->default('no_discount')->after('tax'); // no_discount, before_tax, after_tax
            $table->decimal('discount_percent', 10, 2)->default(0)->after('discount_type');
            $table->decimal('discount_val', 15, 2)->default(0)->after('discount_percent');
            $table->decimal('adjustment', 15, 2)->default(0)->after('discount_val');

            // Payment & currency
            $table->string('currency', 10)->default('USD')->after('adjustment');
            $table->string('allowed_payment_modes')->nullable()->after('currency');
            $table->string('sale_agent')->nullable()->after('allowed_payment_modes');

            // Notes
            $table->text('admin_note')->nullable()->after('notes');
            $table->text('client_note')->nullable()->after('admin_note');
            $table->text('terms_conditions')->nullable()->after('client_note');

            // Display
            $table->string('qty_display_mode')->default('qty')->after('terms_conditions'); // qty, hours, qty_hours
        });

        Schema::table('recurring_invoice_items', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('qty');
            $table->decimal('tax_rate', 8, 2)->default(0)->after('rate');
            $table->integer('order')->default(0)->after('tax_rate');
        });
    }

    public function down(): void
    {
        Schema::table('recurring_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'billing_street', 'billing_city', 'billing_state', 'billing_zip', 'billing_country',
                'shipping_street', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country',
                'discount_type', 'discount_percent', 'discount_val', 'adjustment',
                'currency', 'allowed_payment_modes', 'sale_agent',
                'admin_note', 'client_note', 'terms_conditions', 'qty_display_mode',
            ]);
        });

        Schema::table('recurring_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['unit', 'tax_rate', 'order']);
        });
    }
};
