<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('prevent_overdue_reminders')->default(false)->after('duedate');
            $table->string('allowed_payment_modes')->nullable()->after('prevent_overdue_reminders');
            $table->string('currency')->default('USD')->after('allowed_payment_modes');
            $table->string('sale_agent')->nullable()->after('currency');
            $table->string('recurring_type')->default('no')->after('sale_agent');
            $table->string('discount_type')->default('no_discount')->after('recurring_type');
            $table->text('admin_note')->nullable()->after('discount_type');
            $table->string('qty_display_mode')->default('qty')->after('admin_note');
            $table->text('client_note')->nullable()->after('qty_display_mode');
            $table->text('terms_conditions')->nullable()->after('client_note');
            $table->decimal('adjustment', 15, 2)->default(0.00)->after('tax');

            // Billing address overrides
            $table->string('billing_street')->nullable()->after('terms_conditions');
            $table->string('billing_city')->nullable()->after('billing_street');
            $table->string('billing_state')->nullable()->after('billing_city');
            $table->string('billing_zip')->nullable()->after('billing_state');
            $table->string('billing_country')->nullable()->after('billing_zip');

            // Shipping address overrides
            $table->string('shipping_street')->nullable()->after('billing_country');
            $table->string('shipping_city')->nullable()->after('shipping_street');
            $table->string('shipping_state')->nullable()->after('shipping_city');
            $table->string('shipping_zip')->nullable()->after('shipping_state');
            $table->string('shipping_country')->nullable()->after('shipping_zip');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('qty');
            $table->decimal('tax_rate', 5, 2)->default(0.00)->after('rate');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
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
                'adjustment',
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
            ]);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['unit', 'tax_rate']);
        });
    }
};
