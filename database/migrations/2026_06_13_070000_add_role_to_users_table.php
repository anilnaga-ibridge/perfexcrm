<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('staff')->after('email');
            $table->string('profile_image')->nullable()->after('role');
            $table->boolean('active')->default(true)->after('profile_image');
            $table->string('phone')->nullable()->after('active');
            $table->string('direction')->nullable()->after('phone');
            $table->string('department')->nullable()->after('direction');
        });

        // Update existing admin user to have admin role
        \DB::table('users')->where('email', 'admin@test.com')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'profile_image', 'active', 'phone', 'direction', 'department']);
        });
    }
};
