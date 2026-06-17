<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('tags', 500)->nullable()->after('assigned_to');
            $table->unsignedBigInteger('service_id')->nullable()->after('tags');
            $table->unsignedBigInteger('contact_id')->nullable()->after('service_id');
            $table->string('cc', 500)->nullable()->after('contact_id');
            $table->timestamp('last_reply_at')->nullable()->after('cc');
            $table->integer('views_count')->default(0)->after('last_reply_at');

            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['client_id']);
            $table->dropColumn([
                'tags', 'service_id', 'contact_id', 'cc', 'last_reply_at', 'views_count',
            ]);
        });
    }
};
