<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_mailer_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('mail_engine', 30)->default('PHPMailer');
            $table->string('protocol', 40)->default('SMTP');
            $table->string('encryption', 10)->default('TLS');
            $table->string('smtp_host')->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->string('from_email')->nullable();
            // Encrypted values expand beyond a conventional VARCHAR length.
            $table->text('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('charset', 40)->default('utf-8');
            $table->text('bcc_emails')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_mailer_configurations');
    }
};
