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
use App\Http\Controllers\Api\KbController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\RecurringInvoiceController;
use App\Http\Controllers\Api\ClientFileController;
use App\Http\Controllers\Api\ClientVaultController;
use App\Http\Controllers\Api\ReminderController;

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
    
    // Metadata helper (statuses, sources, staff)
    Route::get('lead-metadata', [LeadFieldController::class, 'index']);
    
    // Staff management
    Route::apiResource('staff', StaffController::class);
    
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
    Route::apiResource('tasks', TaskController::class);

    // Announcements
    Route::apiResource('announcements', AnnouncementController::class);

    // Goals
    Route::apiResource('goals', GoalController::class);

    // Media
    Route::apiResource('media', MediaController::class);

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index']);

    // Expenses
    Route::apiResource('expenses', ExpenseController::class);

    // Projects
    Route::apiResource('projects', ProjectController::class);

    // Contracts
    Route::get('contract-types', [ContractController::class, 'getTypes']);
    Route::apiResource('contracts', ContractController::class);

    // Tickets + replies
    Route::get('tickets/metadata', [TicketController::class, 'metadata']);
    Route::apiResource('tickets', TicketController::class);
    Route::post('tickets/{id}/reply', [TicketController::class, 'addReply']);

    // Estimate Requests
    Route::apiResource('estimate-requests', EstimateRequestController::class);

    // Knowledge Base Articles
    Route::apiResource('kb-articles', KbController::class);

    // Reports
    Route::get('reports/sales',      [ReportController::class, 'sales']);
    Route::get('reports/expenses',   [ReportController::class, 'expenses']);
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
});
