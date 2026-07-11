<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('module_id');
            $table->string('setting_key');
            $table->text('setting_value')->nullable();
            $table->timestamps();

            $table->unique(['module_id', 'setting_key']);
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            $table->index('module_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_settings');
    }
};
