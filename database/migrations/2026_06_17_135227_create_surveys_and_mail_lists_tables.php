<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('view_description')->nullable();
            $table->text('email_description')->nullable();
            $table->boolean('include_link')->default(true);
            $table->string('from_email')->nullable();
            $table->string('redirect_url')->nullable();
            $table->boolean('disabled')->default(false);
            $table->boolean('logged_in_only')->default(false);
            $table->integer('total_questions')->default(0);
            $table->integer('total_participants')->default(0);
            $table->date('date_created')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('mail_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('custom_fields')->nullable();
            $table->string('creator_name')->nullable();
            $table->date('date_created')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
        Schema::dropIfExists('mail_lists');
    }
};
