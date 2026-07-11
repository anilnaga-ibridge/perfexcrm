<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('module_id')->nullable();
            $table->string('module_alias');
            $table->string('event_name');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules')->onDelete('set null');
            $table->index('module_alias');
            $table->index('event_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_events');
    }
};
