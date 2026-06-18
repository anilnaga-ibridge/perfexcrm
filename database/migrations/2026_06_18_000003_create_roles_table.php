<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        // Insert default roles
        $adminPermissions = [];
        $allFeatures = [
            'Bulk PDF Export', 'Contracts', 'Credit Notes', 'Customers',
            'Email Templates', 'Estimates', 'Expenses', 'Invoices', 'Items',
            'Knowledge Base', 'Payments', 'Projects', 'Proposals', 'Reports',
            'Staff Roles', 'Settings', 'Staff', 'Subscriptions', 'Tasks',
            'Task Checklist Templates', 'Estimate Request', 'Leads', 'Surveys',
            'e-Invoice', 'Goals',
        ];
        foreach ($allFeatures as $feature) {
            $adminPermissions[$feature] = ['view' => true, 'create' => true, 'edit' => true, 'delete' => true];
        }

        $employeePermissions = [
            'Invoices'       => ['view_own' => true, 'view_global' => false, 'create' => true, 'edit' => true, 'delete' => false],
            'Estimates'      => ['view_own' => true, 'view_global' => false, 'create' => true, 'edit' => true, 'delete' => false],
            'Expenses'       => ['view_own' => true, 'view_global' => false, 'create' => true, 'edit' => true, 'delete' => false],
            'Customers'      => ['view_own' => false, 'view_global' => true, 'create' => true, 'edit' => true, 'delete' => false],
            'Contracts'      => ['view_own' => true, 'view_global' => false, 'create' => true, 'edit' => true, 'delete' => false],
            'Projects'       => ['view_own' => true, 'view_global' => false, 'create' => true, 'edit' => true, 'delete' => false,
                                 'create_timesheets' => true, 'edit_milestones' => false, 'delete_milestones' => false],
            'Tasks'          => ['view_own' => true, 'view_global' => false, 'create' => true, 'edit' => true, 'delete' => false,
                                 'edit_timesheets_global' => false, 'edit_own_timesheets' => true, 'delete_timesheets_global' => false, 'delete_own_timesheets' => true],
            'Leads'          => ['view_global' => true, 'delete' => false],
            'Payments'       => ['view_own' => true, 'view_global' => false, 'create' => true, 'edit' => true, 'delete' => false],
            'Proposals'      => ['view_own' => true, 'view_global' => false, 'create' => true, 'edit' => true, 'delete' => false],
            'Credit Notes'   => ['view_own' => true, 'view_global' => false, 'create' => true, 'edit' => true, 'delete' => false],
            'Items'          => ['view_global' => true, 'create' => true, 'edit' => true, 'delete' => false],
            'Knowledge Base' => ['view_global' => true, 'create' => true, 'edit' => true, 'delete' => false],
            'Subscriptions'  => ['view_own' => true, 'view_global' => false, 'create' => true, 'edit' => true, 'delete' => false],
            'Reports'        => ['view_global' => true, 'view_timesheets' => true],
            'Surveys'        => ['view_global' => true, 'create' => true, 'edit' => true, 'delete' => false],
            'Goals'          => ['view_global' => true, 'create' => true, 'edit' => true, 'delete' => false],
            'Staff'          => ['view_global' => false, 'create' => false, 'edit' => false, 'delete' => false],
            'Settings'       => ['view_global' => false, 'edit' => false],
            'Staff Roles'    => ['view_global' => false, 'create' => false, 'edit' => false, 'delete' => false],
        ];

        \DB::table('roles')->insert([
            ['name' => 'Administrator', 'slug' => 'admin', 'description' => 'Full access to all features and settings', 'permissions' => json_encode($adminPermissions), 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Employee', 'slug' => 'employee', 'description' => 'Standard staff access with configurable permissions', 'permissions' => json_encode($employeePermissions), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
