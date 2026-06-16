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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->date('date');
            $table->string('status')->default('Open'); // Open, Closed, Void
            $table->string('reference')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();
        });

        Schema::create('predefined_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('rate', 15, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->string('unit')->default('Unit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predefined_items');
        Schema::dropIfExists('credit_notes');
    }
};
