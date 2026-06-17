<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('progress_from_tasks')->default(false)->after('budget');
            $table->integer('progress')->default(0)->after('progress_from_tasks');
            $table->decimal('estimated_hours', 8, 2)->nullable()->after('progress');
            $table->boolean('send_created_email')->default(false)->after('estimated_hours');
            $table->string('tags')->nullable()->after('send_created_email');
            $table->json('settings')->nullable()->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'progress_from_tasks',
                'progress',
                'estimated_hours',
                'send_created_email',
                'tags',
                'settings'
            ]);
        });
    }
};
