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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('message');
            $table->boolean('show_to_staff')->default(true);
            $table->boolean('show_to_clients')->default(false);
            $table->timestamps();
        });

        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('goal_type'); // e.g. "invoice_amount", "lead_conversion", "tickets_solved"
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('target_value', 15, 2)->default(0.00);
            $table->decimal('current_value', 15, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_path')->nullable();
            $table->boolean('is_directory')->default(false);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->string('mime_type')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('media_items')->onDelete('cascade');
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('media_items');
        Schema::dropIfExists('goals');
        Schema::dropIfExists('announcements');
    }
};
