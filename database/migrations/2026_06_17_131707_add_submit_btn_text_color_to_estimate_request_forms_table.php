<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimate_request_forms', function (Blueprint $table) {
            $table->string('submit_btn_text_color')->default('#ffffff');
        });
    }

    public function down(): void
    {
        Schema::table('estimate_request_forms', function (Blueprint $table) {
            $table->dropColumn('submit_btn_text_color');
        });
    }
};
