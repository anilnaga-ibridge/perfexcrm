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
        Schema::table('modules', function (Blueprint $table) {
            $table->string('sidebar_label')->nullable()->after('permissions');
            $table->string('sidebar_path')->nullable()->after('sidebar_label');
            $table->string('sidebar_icon')->nullable()->after('sidebar_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['sidebar_label', 'sidebar_path', 'sidebar_icon']);
        });
    }
};
