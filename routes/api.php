<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\LeadFieldController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CreditNoteController;
use App\Http\Controllers\Api\PredefinedItemController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\EstimateRequestController;
use App\Http\Controllers\Api\EstimateRequestFormController;
use App\Http\Controllers\Api\KbController;
use App\Http\Controllers\Api\KbCategoryController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\RecurringInvoiceController;
use App\Http\Controllers\Api\ClientFileController;
use App\Http\Controllers\Api\ClientVaultController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\MailListController;
use App\Http\Controllers\Api\DatabaseBackupController;
use App\Http\Controllers\Api\TicketPipeLogController;
use App\Http\Controllers\Api\SalesReportController;
use App\Http\Controllers\Api\RoleController;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard metrics
    Route::get('dashboard-metrics', [DashboardController::class, 'index']);

    // Customers (Clients) CRUD API
    Route::apiResource('clients', ClientController::class);
    Route::put('clients/{id}/status', [ClientController::class, 'updateStatus']);
    
    // Contacts API
    Route::get('contacts', [ContactController::class, 'index']);
    Route::post('contacts', [ContactController::class, 'store']);
    Route::put('contacts/{id}', [ContactController::class, 'update']);
    Route::delete('contacts/{id}', [ContactController::class, 'destroy']);
    Route::put('contacts/{id}/status', [ContactController::class, 'updateStatus']);
    
    // Leads Pipeline CRUD API
    Route::apiResource('leads', LeadController::class);
    Route::put('leads/{id}/status', [LeadController::class, 'updateStatus']);
    Route::post('leads/import', [LeadController::class, 'import']);
    
    // Metadata helper (statuses, sources, staff)
    Route::get('lead-metadata', [LeadFieldController::class, 'index']);
    
    // Staff management
    Route::get('staff/roles/list', [StaffController::class, 'roles']);
    Route::apiResource('staff', StaffController::class);
    Route::post('staff/{id}/image', [StaffController::class, 'uploadImage']);

    // Roles
    Route::apiResource('roles', RoleController::class);
    
    // Invoices
    Route::apiResource('invoices', InvoiceController::class);

    // Recurring Invoices
    Route::apiResource('recurring-invoices', RecurringInvoiceController::class);

    // Payments
    Route::apiResource('payments', PaymentController::class);

    // Credit Notes
    Route::apiResource('credit-notes', CreditNoteController::class);

    // Predefined Catalog Items
    Route::apiResource('predefined-items', PredefinedItemController::class);

    // Subscriptions
    Route::apiResource('subscriptions', SubscriptionController::class);

    // Tasks
    Route::get('tasks/overview', [TaskController::class, 'overview']);
    Route::apiResource('tasks', TaskController::class);

    // Announcements
    Route::apiResource('announcements', AnnouncementController::class);

    // Goals
    Route::apiResource('goals', GoalController::class);

    // Surveys
    Route::apiResource('surveys', SurveyController::class);

    // Mail Lists
    Route::apiResource('mail-lists', MailListController::class);

    // Media
    Route::apiResource('media', MediaController::class);

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index']);
    Route::delete('activity-logs', [ActivityLogController::class, 'destroy']);

    // Expenses
    Route::apiResource('expenses', ExpenseController::class);

    // Projects
    Route::apiResource('projects', ProjectController::class);

    // Contracts
    Route::get('contract-types', [ContractController::class, 'getTypes']);
    Route::apiResource('contracts', ContractController::class);

    // Tickets + replies
    Route::get('tickets/metadata', [TicketController::class, 'metadata']);
    Route::get('tickets/weekly-stats', [TicketController::class, 'weeklyStats']);
    Route::apiResource('tickets', TicketController::class);
    Route::post('tickets/{id}/reply', [TicketController::class, 'addReply']);

    // Estimate Requests
    Route::apiResource('estimate-requests', EstimateRequestController::class);
    Route::apiResource('estimate-request-forms', EstimateRequestFormController::class);

    // Knowledge Base
    Route::get('kb-articles/report', [KbController::class, 'report']);
    Route::apiResource('kb-articles', KbController::class);
    Route::apiResource('kb-categories', KbCategoryController::class);

    // Reports
    Route::get('reports/sales',      [ReportController::class, 'sales']);
    Route::get('reports/expenses',   [ReportController::class, 'expenses']);
    Route::get('reports/expenses-detailed', [ReportController::class, 'expensesDetailed']);
    Route::get('reports/finance',    [ReportController::class, 'finance']);
    Route::get('reports/team',       [ReportController::class, 'team']);

    // Client Files
    Route::get('clients/{client_id}/files', [ClientFileController::class, 'index']);
    Route::post('clients/{client_id}/files', [ClientFileController::class, 'store']);
    Route::put('client-files/{id}/status', [ClientFileController::class, 'updateStatus']);
    Route::delete('client-files/{id}', [ClientFileController::class, 'destroy']);

    // Client Vaults
    Route::get('clients/{client_id}/vaults', [ClientVaultController::class, 'index']);
    Route::post('clients/{client_id}/vaults', [ClientVaultController::class, 'store']);
    Route::put('vault-entries/{id}', [ClientVaultController::class, 'update']);
    Route::delete('vault-entries/{id}', [ClientVaultController::class, 'destroy']);
    Route::get('vault-entries/{id}/decrypt', [ClientVaultController::class, 'decrypt']);

    // Client Reminders
    Route::get('clients/{client_id}/reminders', [ReminderController::class, 'index']);
    Route::post('clients/{client_id}/reminders', [ReminderController::class, 'store']);
    Route::delete('reminders/{id}', [ReminderController::class, 'destroy']);

    // Database Backups
    Route::get('database-backups', [DatabaseBackupController::class, 'index']);
    Route::post('database-backups', [DatabaseBackupController::class, 'store']);
    Route::get('database-backups/{id}/download', [DatabaseBackupController::class, 'download']);
    Route::delete('database-backups/{id}', [DatabaseBackupController::class, 'destroy']);
    Route::post('database-backups/toggle-auto', [DatabaseBackupController::class, 'toggleAutoBackup']);

    // Ticket Pipe Logs
    Route::get('ticket-pipe-logs', [TicketPipeLogController::class, 'index']);
    Route::delete('ticket-pipe-logs/clear', [TicketPipeLogController::class, 'clear']);
    Route::delete('ticket-pipe-logs/{id}', [TicketPipeLogController::class, 'destroy']);

    // Sales Reports
    Route::get('sales-report/invoices', [SalesReportController::class, 'invoices']);
    Route::get('sales-report/items', [SalesReportController::class, 'items']);
    Route::get('sales-report/payments', [SalesReportController::class, 'payments']);
    Route::get('sales-report/credit-notes', [SalesReportController::class, 'creditNotes']);
    Route::get('sales-report/proposals', [SalesReportController::class, 'proposals']);
    Route::get('sales-report/estimates', [SalesReportController::class, 'estimates']);
    Route::get('sales-report/customers', [SalesReportController::class, 'customers']);
    Route::get('sales-report/charts', [SalesReportController::class, 'charts']);
    Route::get('sales-report/total-income', [SalesReportController::class, 'totalIncome']);
    Route::get('sales-report/payment-modes', [SalesReportController::class, 'paymentModes']);
    Route::get('sales-report/customer-groups', [SalesReportController::class, 'customerGroups']);
});
