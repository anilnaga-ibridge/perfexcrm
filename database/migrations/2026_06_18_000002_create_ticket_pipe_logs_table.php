<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_pipe_logs', function (Blueprint $table) {
            $table->id();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('to')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('Imported');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_pipe_logs');
    }
};
