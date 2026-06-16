<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('amount');
            $table->string('currency')->default('USD')->after('quantity');
            $table->string('tax_1')->nullable()->after('currency');
            $table->string('tax_2')->nullable()->after('tax_1');
            $table->text('terms')->nullable()->after('tax_2');
            $table->text('description')->nullable()->after('terms');
            $table->boolean('include_description')->default(false)->after('description');
            $table->foreignId('project_id')->nullable()->after('client_id')->constrained('projects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn([
                'quantity',
                'currency',
                'tax_1',
                'tax_2',
                'terms',
                'description',
                'include_description',
                'project_id'
            ]);
        });
    }
};
