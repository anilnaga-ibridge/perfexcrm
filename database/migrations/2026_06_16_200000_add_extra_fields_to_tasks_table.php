<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->after('id');
            $table->boolean('billable')->default(false)->after('related_to_id');
            $table->boolean('is_public')->default(false)->after('billable');
            $table->decimal('hourly_rate', 10, 2)->default(0)->after('is_public');
            $table->string('repeat_every', 50)->nullable()->after('hourly_rate');
            $table->string('tags', 500)->nullable()->after('repeat_every');
            // JSON arrays for multi-assignees and followers (staff IDs)
            $table->json('assignees')->nullable()->after('tags');
            $table->json('followers')->nullable()->after('assignees');

            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['client_id']);
            $table->dropColumn([
                'client_id', 'billable', 'is_public', 'hourly_rate',
                'repeat_every', 'tags', 'assignees', 'followers',
            ]);
        });
    }
};
