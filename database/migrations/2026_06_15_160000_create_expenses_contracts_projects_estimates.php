<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expense categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('note')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('date');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->string('reference')->nullable();
            $table->string('payment_mode')->default('Credit Card');
            $table->string('status')->default('unbilled'); // unbilled, billed
            $table->boolean('billable')->default(false);
            $table->timestamps();
        });

        // Contract types
        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Contracts
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->unsignedBigInteger('contract_type_id')->nullable();
            $table->decimal('value', 12, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('Not Started');
            $table->text('description')->nullable();
            $table->boolean('signed')->default(false);
            $table->timestamps();
        });

        // Projects
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->string('billing_type')->default('Fixed Rate');
            $table->decimal('budget', 12, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable();
            $table->string('status')->default('Not Started');
            $table->timestamps();
        });

        // Project members (pivot)
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Estimates (linking to clients)
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable()->unique();
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->date('date');
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('Draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 8, 2)->default(0);
            $table->string('discount_type')->default('percent');
            $table->decimal('tax', 8, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->text('note')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();
        });

        // Estimate line items
        Schema::create('estimate_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->constrained('estimates')->onDelete('cascade');
            $table->string('description');
            $table->text('long_description')->nullable();
            $table->decimal('qty', 8, 2)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('tax', 8, 2)->default(0);
            $table->decimal('tax2', 8, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_items');
        Schema::dropIfExists('estimates');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('contract_types');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
