<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->string('status')->default('active'); // active, paused, stopped
            $table->string('frequency')->default('monthly'); // daily, weekly, monthly, quarterly, yearly
            $table->integer('cycles')->default(0); // 0 = unlimited
            $table->integer('cycles_run')->default(0);
            $table->date('date_from');
            $table->date('date_to')->nullable();
            $table->date('next_invoice_date')->nullable();
            $table->date('last_sent_at')->nullable();
            // Financial
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();
        });

        Schema::create('recurring_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_invoice_id')->constrained('recurring_invoices')->onDelete('cascade');
            $table->string('description');
            $table->text('long_description')->nullable();
            $table->decimal('qty', 12, 2)->default(1.00);
            $table->decimal('rate', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_invoice_items');
        Schema::dropIfExists('recurring_invoices');
    }
};
