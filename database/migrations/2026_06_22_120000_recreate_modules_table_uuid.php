<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('modules');

        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('alias')->unique();
            $table->string('version')->nullable();
            $table->string('minimum_core_version')->nullable();
            $table->json('depends')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('installed'); // installed, active, inactive
            $table->string('author')->nullable();
            $table->timestamps();
        });

        Schema::create('module_menus', function (Blueprint $table) {
            $table->id();
            $table->uuid('module_id');
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->string('title');
            $table->string('route');
            $table->string('icon')->nullable();
            $table->string('permission')->nullable();
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('module_menus')->onDelete('cascade');
        });

        Schema::create('module_permissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('module_id');
            $table->string('permission_name')->unique();
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_permissions');
        Schema::dropIfExists('module_menus');
        Schema::dropIfExists('modules');
    }
};
