<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_member')->nullable()->after('goal_type');
            $table->text('description')->nullable()->after('end_date');
            $table->boolean('notify_when_achieve')->default(true)->after('current_value');
            $table->boolean('notify_when_fail')->default(true)->after('notify_when_achieve');
        });
    }

    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn(['staff_member', 'description', 'notify_when_achieve', 'notify_when_fail']);
        });
    }
};
