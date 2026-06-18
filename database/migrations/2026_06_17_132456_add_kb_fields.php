<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kb_categories', function (Blueprint $table) {
            $table->string('color')->default('#6b7280');
            $table->text('description')->nullable();
            $table->integer('order_num')->default(0);
            $table->boolean('disabled')->default(false);
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            $table->boolean('internal')->default(false);
            $table->boolean('disabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('kb_categories', function (Blueprint $table) {
            $table->dropColumn(['color', 'description', 'order_num', 'disabled']);
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropColumn(['internal', 'disabled']);
        });
    }
};
