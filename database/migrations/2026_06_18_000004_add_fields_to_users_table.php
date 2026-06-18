<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete()->after('role');
            $table->decimal('hourly_rate', 10, 2)->default(0)->after('department');
            $table->string('facebook')->nullable()->after('hourly_rate');
            $table->string('linkedin')->nullable()->after('facebook');
            $table->string('skype')->nullable()->after('linkedin');
            $table->string('default_language')->nullable()->after('skype');
            $table->text('email_signature')->nullable()->after('default_language');
        });

        // Assign existing users to appropriate role
        $adminRole = \DB::table('roles')->where('slug', 'admin')->value('id');
        $empRole   = \DB::table('roles')->where('slug', 'employee')->value('id');
        if ($adminRole) {
            \DB::table('users')->where('role', 'admin')->update(['role_id' => $adminRole]);
        }
        if ($empRole) {
            \DB::table('users')->whereIn('role', ['staff', 'manager'])->update(['role_id' => $empRole]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'hourly_rate', 'facebook', 'linkedin', 'skype', 'default_language', 'email_signature']);
        });
    }
};
