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
        Schema::table('todo_items', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_to')->nullable()->after('staff_id');
            $table->unsignedBigInteger('assigned_by')->nullable()->after('assigned_to');
            $table->string('priority', 20)->default('medium')->after('description');
            $table->date('due_date')->nullable()->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('todo_items', function (Blueprint $table) {
            $table->dropColumn(['assigned_to', 'assigned_by', 'priority', 'due_date']);
        });
    }
};
