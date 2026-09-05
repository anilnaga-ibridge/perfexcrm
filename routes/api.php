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
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\HrPayrollController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\Api\EmailMailerConfigurationController;

Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    
    // Social Authentication Routes
    Route::get('social/{provider}/redirect', [\App\Http\Controllers\Api\Auth\SocialLoginController::class, 'redirect']);
    Route::get('social/{provider}/callback', [\App\Http\Controllers\Api\Auth\SocialLoginController::class, 'callback']);
    Route::post('social/exchange', [\App\Http\Controllers\Api\Auth\SocialLoginController::class, 'exchange']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // SMTP configuration is stored separately from email template/theme data.
    // The API never returns the encrypted SMTP password.
    Route::middleware('permission:Settings.view')->get('email-mailer-configuration', [EmailMailerConfigurationController::class, 'show']);
    Route::middleware('permission:Settings.edit')->put('email-mailer-configuration', [EmailMailerConfigurationController::class, 'update']);

    // Email Templates API
    Route::get('email-templates', [\App\Http\Controllers\Api\EmailTemplateController::class, 'index']);
    Route::post('email-templates', [\App\Http\Controllers\Api\EmailTemplateController::class, 'store']);
    Route::post('email-templates/bulk', [\App\Http\Controllers\Api\EmailTemplateController::class, 'bulkStore']);
    Route::post('email-templates/upload-logo', [\App\Http\Controllers\Api\EmailTemplateController::class, 'uploadLogo']);

    // Dashboard metrics
    Route::get('dashboard-metrics', [DashboardController::class, 'index']);

    // Notifications API
    Route::get('notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('notifications', [\App\Http\Controllers\Api\NotificationController::class, 'store']);
    Route::post('notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllRead']);
    Route::post('notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markRead']);

    // To-Do Items CRUD
    Route::get('todos', [TodoController::class, 'index']);
    Route::post('todos', [TodoController::class, 'store']);
    Route::put('todos/{id}', [TodoController::class, 'update']);
    Route::delete('todos/{id}', [TodoController::class, 'destroy']);
    Route::put('todos-reorder', [TodoController::class, 'reorder']);

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

    // Plugins (primary routes)
    Route::apiResource('plugins', ModuleController::class)->only(['index', 'store', 'destroy']);
    Route::get('plugins/sso-url', [ModuleController::class, 'ssoUrl']);
    Route::get('plugins/active', [ModuleController::class, 'active']);
    Route::get('plugins/menus', [ModuleController::class, 'menus']);
    Route::match(['patch', 'put'], 'plugins/{id}/toggle', [ModuleController::class, 'toggleStatus']);
    Route::match(['patch', 'put'], 'plugins/{id}/toggle-status', [ModuleController::class, 'toggleStatus']);
    Route::match(['patch', 'put'], 'plugins/{id}/deactivate', [ModuleController::class, 'deactivate']);
    Route::match(['patch', 'put'], 'plugins/{id}/activate', [ModuleController::class, 'activate']);
    Route::get('plugins/{alias}/settings', [ModuleController::class, 'getSettings']);
    Route::match(['put', 'post'], 'plugins/{alias}/settings', [ModuleController::class, 'saveSettings']);
    Route::delete('plugins/{alias}/settings', [ModuleController::class, 'resetSettings']);
    Route::match(['delete', 'post'], 'plugins/{alias}/settings/reset', [ModuleController::class, 'resetSettings']);
    Route::post('plugins/{id}/repair', [ModuleController::class, 'repair']);
    Route::post('plugins/{id}/rollback', [ModuleController::class, 'rollback']);
    Route::post('plugins/sync-filesystem', [ModuleController::class, 'syncFromFilesystem']);
    Route::get('plugins/{plugin}/metadata', function ($plugin) {
        $meta = app(\App\Plugin\Metadata\PluginMetadata::class)->get($plugin);
        if (!$meta) {
            return response()->json(['error' => 'Plugin not found'], 404);
        }
        return response()->json($meta);
    });

    // Modules (backward-compat aliases — deprecated, use /plugins instead)
    Route::apiResource('modules', ModuleController::class)->only(['index', 'store', 'destroy']);
    Route::get('modules/sso-url', [ModuleController::class, 'ssoUrl']);
    Route::get('modules/active', [ModuleController::class, 'active']);
    Route::get('modules/menus', [ModuleController::class, 'menus']);
    Route::match(['patch', 'put'], 'modules/{id}/toggle', [ModuleController::class, 'toggleStatus']);
    Route::match(['patch', 'put'], 'modules/{id}/toggle-status', [ModuleController::class, 'toggleStatus']);
    Route::match(['patch', 'put'], 'modules/{id}/deactivate', [ModuleController::class, 'deactivate']);
    Route::match(['patch', 'put'], 'modules/{id}/activate', [ModuleController::class, 'activate']);
    Route::get('modules/{alias}/settings', [ModuleController::class, 'getSettings']);
    Route::match(['put', 'post'], 'modules/{alias}/settings', [ModuleController::class, 'saveSettings']);
    Route::delete('modules/{alias}/settings', [ModuleController::class, 'resetSettings']);
    Route::match(['delete', 'post'], 'modules/{alias}/settings/reset', [ModuleController::class, 'resetSettings']);
    Route::post('modules/{id}/repair', [ModuleController::class, 'repair']);
    Route::post('modules/{id}/rollback', [ModuleController::class, 'rollback']);
    Route::get('modules/{plugin}/metadata', function ($plugin) {
        $meta = app(\App\Plugin\Metadata\PluginMetadata::class)->get($plugin);
        if (!$meta) {
            return response()->json(['error' => 'Plugin not found'], 404);
        }
        return response()->json($meta);
    });
    
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
    Route::get('tickets/metadata', [TicketController::class, 'metadata'])->middleware('permission:Support.view');
    Route::get('tickets/weekly-stats', [TicketController::class, 'weeklyStats'])->middleware('permission:Support.view');
    Route::apiResource('tickets', TicketController::class);
    Route::post('tickets/{id}/reply', [TicketController::class, 'addReply']);

    // Estimate Requests
    Route::apiResource('estimate-requests', EstimateRequestController::class);
    Route::apiResource('estimate-request-forms', EstimateRequestFormController::class);

    // Knowledge Base
    Route::get('kb-articles/report', [KbController::class, 'report'])->middleware('permission:Knowledge Base.view');
    Route::apiResource('kb-articles', KbController::class);
    Route::apiResource('kb-categories', KbCategoryController::class);

    // Reports
    Route::middleware('permission:Reports.view')->group(function () {
        Route::get('reports/sales',      [ReportController::class, 'sales']);
        Route::get('reports/leads',      [ReportController::class, 'leads']);
        Route::get('reports/expenses',   [ReportController::class, 'expenses']);
        Route::get('reports/expenses-detailed', [ReportController::class, 'expensesDetailed']);
        Route::get('reports/finance',    [ReportController::class, 'finance']);
        Route::get('reports/team',       [ReportController::class, 'team']);

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
    Route::middleware('permission:Settings.view')->group(function () {
        Route::get('database-backups', [DatabaseBackupController::class, 'index']);
        Route::post('database-backups', [DatabaseBackupController::class, 'store']);
        Route::get('database-backups/{id}/download', [DatabaseBackupController::class, 'download']);
        Route::delete('database-backups/{id}', [DatabaseBackupController::class, 'destroy']);
        Route::post('database-backups/toggle-auto', [DatabaseBackupController::class, 'toggleAutoBackup']);
    });

    // Ticket Pipe Logs
    Route::get('ticket-pipe-logs', [TicketPipeLogController::class, 'index']);
    Route::delete('ticket-pipe-logs/clear', [TicketPipeLogController::class, 'clear']);
    Route::delete('ticket-pipe-logs/{id}', [TicketPipeLogController::class, 'destroy']);

    // ─── HR Payroll (hr-payroll module CI compat) ────────────────────────────────

    // Settings
    Route::get('hr-payroll/settings', [HrPayrollController::class, 'settings']);
    Route::get('hr-payroll/settings/payroll-columns', [HrPayrollController::class, 'getPayrollColumns']);
    Route::post('hr-payroll/settings/payroll-columns', [HrPayrollController::class, 'storePayrollColumn']);
    Route::put('hr-payroll/settings/payroll-columns/{id}', [HrPayrollController::class, 'updatePayrollColumn']);
    Route::delete('hr-payroll/settings/payroll-columns/{id}', [HrPayrollController::class, 'deletePayrollColumn']);

    Route::get('hr-payroll/settings/earnings', [HrPayrollController::class, 'getEarnings']);
    Route::post('hr-payroll/settings/earnings', [HrPayrollController::class, 'storeEarning']);
    Route::put('hr-payroll/settings/earnings/{id}', [HrPayrollController::class, 'updateEarning']);
    Route::delete('hr-payroll/settings/earnings/{id}', [HrPayrollController::class, 'deleteEarning']);

    Route::get('hr-payroll/settings/deductions', [HrPayrollController::class, 'getDeductionsList']);
    Route::post('hr-payroll/settings/deductions', [HrPayrollController::class, 'storeDeductionList']);
    Route::put('hr-payroll/settings/deductions/{id}', [HrPayrollController::class, 'updateDeductionList']);
    Route::delete('hr-payroll/settings/deductions/{id}', [HrPayrollController::class, 'deleteDeductionList']);

    Route::get('hr-payroll/settings/insurance', [HrPayrollController::class, 'getInsuranceList']);
    Route::post('hr-payroll/settings/insurance', [HrPayrollController::class, 'storeInsuranceListItem']);
    Route::put('hr-payroll/settings/insurance/{id}', [HrPayrollController::class, 'updateInsuranceListItem']);
    Route::delete('hr-payroll/settings/insurance/{id}', [HrPayrollController::class, 'deleteInsuranceListItem']);

    Route::get('hr-payroll/settings/income-tax-rates', [HrPayrollController::class, 'getIncomeTaxRates']);
    Route::post('hr-payroll/settings/income-tax-rates', [HrPayrollController::class, 'storeIncomeTaxRate']);
    Route::put('hr-payroll/settings/income-tax-rates/{id}', [HrPayrollController::class, 'updateIncomeTaxRate']);
    Route::delete('hr-payroll/settings/income-tax-rates/{id}', [HrPayrollController::class, 'deleteIncomeTaxRate']);

    Route::get('hr-payroll/settings/income-tax-rebates', [HrPayrollController::class, 'getIncomeTaxRebates']);
    Route::post('hr-payroll/settings/income-tax-rebates', [HrPayrollController::class, 'storeIncomeTaxRebate']);
    Route::put('hr-payroll/settings/income-tax-rebates/{id}', [HrPayrollController::class, 'updateIncomeTaxRebate']);
    Route::delete('hr-payroll/settings/income-tax-rebates/{id}', [HrPayrollController::class, 'deleteIncomeTaxRebate']);

    // Employees
    Route::get('hr-payroll/employees', [HrPayrollController::class, 'getEmployees']);
    Route::post('hr-payroll/employees', [HrPayrollController::class, 'storeEmployees']);
    Route::post('hr-payroll/employees/filter', [HrPayrollController::class, 'filterEmployees']);
    Route::post('hr-payroll/employees/copy', [HrPayrollController::class, 'copyEmployees']);

    // Attendance
    Route::get('hr-payroll/attendance', [HrPayrollController::class, 'getAttendance']);
    Route::post('hr-payroll/attendance', [HrPayrollController::class, 'storeAttendance']);
    Route::post('hr-payroll/attendance/filter', [HrPayrollController::class, 'filterAttendance']);
    Route::post('hr-payroll/attendance/calculation', [HrPayrollController::class, 'calculateAttendance']);

    // Deductions
    Route::get('hr-payroll/deductions', [HrPayrollController::class, 'getDeductions']);
    Route::post('hr-payroll/deductions', [HrPayrollController::class, 'storeDeductions']);
    Route::post('hr-payroll/deductions/filter', [HrPayrollController::class, 'filterDeductions']);

    // Commissions
    Route::get('hr-payroll/commissions', [HrPayrollController::class, 'getCommissions']);
    Route::post('hr-payroll/commissions', [HrPayrollController::class, 'storeCommissions']);
    Route::post('hr-payroll/commissions/filter', [HrPayrollController::class, 'filterCommissions']);

    // Insurance
    Route::get('hr-payroll/insurances', [HrPayrollController::class, 'getInsurances']);
    Route::post('hr-payroll/insurances', [HrPayrollController::class, 'storeInsurances']);
    Route::post('hr-payroll/insurances/filter', [HrPayrollController::class, 'filterInsurances']);

    // Bonuses / KPI
    Route::get('hr-payroll/bonuses', [HrPayrollController::class, 'getBonuses']);
    Route::post('hr-payroll/bonuses', [HrPayrollController::class, 'storeBonuses']);
    Route::post('hr-payroll/bonuses/filter', [HrPayrollController::class, 'filterBonuses']);

    // Income Tax
    Route::get('hr-payroll/income-tax', [HrPayrollController::class, 'getIncomeTax']);

    // Payslips
    Route::get('hr-payroll/payslips', [HrPayrollController::class, 'getPayslips']);
    Route::post('hr-payroll/payslips', [HrPayrollController::class, 'storePayslip']);
    Route::get('hr-payroll/payslips/{id}', [HrPayrollController::class, 'getPayslip']);
    Route::put('hr-payroll/payslips/{id}', [HrPayrollController::class, 'updatePayslip']);
    Route::delete('hr-payroll/payslips/{id}', [HrPayrollController::class, 'deletePayslip']);
    Route::post('hr-payroll/payslips/{id}/close', [HrPayrollController::class, 'closePayslip']);
    Route::put('hr-payroll/payslips/{id}/status', [HrPayrollController::class, 'updatePayslipStatus']);
    Route::get('hr-payroll/payslips/{id}/details', [HrPayrollController::class, 'getPayslipDetails']);

    // Payslip Templates
    Route::get('hr-payroll/payslip-templates', [HrPayrollController::class, 'getPayslipTemplates']);
    Route::post('hr-payroll/payslip-templates', [HrPayrollController::class, 'storePayslipTemplate']);
    Route::get('hr-payroll/payslip-templates/{id}', [HrPayrollController::class, 'getPayslipTemplate']);
    Route::put('hr-payroll/payslip-templates/{id}', [HrPayrollController::class, 'updatePayslipTemplate']);
    Route::delete('hr-payroll/payslip-templates/{id}', [HrPayrollController::class, 'deletePayslipTemplate']);

    // Currency Rates
    Route::get('hr-payroll/currency-rates', [HrPayrollController::class, 'getCurrencyRates']);
    Route::post('hr-payroll/currency-rates', [HrPayrollController::class, 'storeCurrencyRate']);
    Route::put('hr-payroll/currency-rates/{id}', [HrPayrollController::class, 'updateCurrencyRate']);
    Route::delete('hr-payroll/currency-rates/{id}', [HrPayrollController::class, 'deleteCurrencyRate']);
    Route::post('hr-payroll/currency-rates/online', [HrPayrollController::class, 'getOnlineCurrencyRates']);

    // Reports
    Route::get('hr-payroll/reports', [HrPayrollController::class, 'getReports']);
    Route::post('hr-payroll/reports/income-summary', [HrPayrollController::class, 'incomeSummaryReport']);
    Route::post('hr-payroll/reports/insurance-summary', [HrPayrollController::class, 'insuranceSummaryReport']);
    Route::post('hr-payroll/reports/payslip-chart', [HrPayrollController::class, 'payslipChartReport']);
    Route::post('hr-payroll/reports/department-chart', [HrPayrollController::class, 'departmentChartReport']);

    // Timesheet Leaves
    Route::get('hr-payroll/timesheet-leaves', [HrPayrollController::class, 'getTimesheetLeaves']);
    Route::post('hr-payroll/timesheet-leaves', [HrPayrollController::class, 'storeTimesheetLeaves']);
    Route::post('hr-payroll/timesheet-leaves/filter', [HrPayrollController::class, 'filterTimesheetLeaves']);
    Route::post('hr-payroll/timesheet-leaves/calculation', [HrPayrollController::class, 'calculateTimesheetLeaves']);

    // Permissions
    Route::get('hr-payroll/permissions', [HrPayrollController::class, 'getPermissions']);
    Route::post('hr-payroll/permissions', [HrPayrollController::class, 'storePermissions']);
    Route::delete('hr-payroll/permissions/{id}', [HrPayrollController::class, 'deletePermission']);

    // Staff, Departments, Roles (reference data)
    Route::get('hr-payroll/staff', [HrPayrollController::class, 'getStaffList']);
    Route::get('hr-payroll/staff/{id}', [HrPayrollController::class, 'getStaffMember']);
    Route::get('hr-payroll/departments', [HrPayrollController::class, 'getDepartments']);
    Route::get('hr-payroll/roles', [HrPayrollController::class, 'getRoles']);

    // Data Integration
    Route::get('hr-payroll/data-integration', [HrPayrollController::class, 'getDataIntegration']);
    Route::post('hr-payroll/data-integration', [HrPayrollController::class, 'storeDataIntegration']);

    // PDF Templates
    Route::get('hr-payroll/pdf-templates', [HrPayrollController::class, 'getPdfTemplates']);
    Route::post('hr-payroll/pdf-templates', [HrPayrollController::class, 'storePdfTemplate']);
    Route::get('hr-payroll/pdf-templates/{id}', [HrPayrollController::class, 'getPdfTemplate']);
    Route::put('hr-payroll/pdf-templates/{id}', [HrPayrollController::class, 'updatePdfTemplate']);
    Route::delete('hr-payroll/pdf-templates/{id}', [HrPayrollController::class, 'deletePdfTemplate']);
});
