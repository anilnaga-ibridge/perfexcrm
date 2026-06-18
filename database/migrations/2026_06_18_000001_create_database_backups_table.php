<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('file_path');
            $table->string('backup_size')->nullable();
            $table->boolean('is_auto_flag')->default(false);
            $table->boolean('auto_backup')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_backups');
    }
};
