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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contact Name
            $table->string('title')->nullable(); // Job Title
            $table->string('company')->nullable(); // Company Name
            $table->text('description')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('phonenumber')->nullable();
            $table->decimal('lead_value', 12, 2)->default(0.00);
            
            // Location
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            
            // Relational Fields
            $table->foreignId('status_id')->constrained('lead_statuses')->onDelete('cascade');
            $table->foreignId('source_id')->constrained('lead_sources')->onDelete('cascade');
            $table->foreignId('assigned_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamp('last_contacted')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
