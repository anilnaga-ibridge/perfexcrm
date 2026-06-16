<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('number');
            $table->string('status')->default('Draft'); // Draft, Sent, Unpaid, Partially Paid, Overdue, Paid
            $table->date('date');
            $table->date('duedate')->nullable();
            
            // Financial calculations
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->decimal('discount_val', 15, 2)->default(0.00);
            $table->decimal('tax', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->string('description');
            $table->text('long_description')->nullable();
            $table->decimal('qty', 12, 2)->default(1.00);
            $table->decimal('rate', 15, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('paymentmode')->nullable(); // Bank, PayPal, Stripe, Cash
            $table->date('date');
            $table->string('transactionid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
