<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimate_request_forms', function (Blueprint $table) {
            $table->string('submit_btn_text')->default('Submit');
            $table->string('submit_btn_bg_color')->default('#84c529');
            $table->string('submission_action')->default('message');
            $table->text('submission_message')->nullable();
            $table->string('submission_redirect_url')->nullable();
            $table->boolean('notify_enabled')->default(false);
            $table->string('notify_type')->default('specific');
            $table->text('notify_staff_ids')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('estimate_request_forms', function (Blueprint $table) {
            $table->dropColumn([
                'submit_btn_text', 'submit_btn_bg_color',
                'submission_action', 'submission_message', 'submission_redirect_url',
                'notify_enabled', 'notify_type', 'notify_staff_ids',
            ]);
        });
    }
};
