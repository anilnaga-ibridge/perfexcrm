<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ApiDataSeederCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:seed-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe database and seed dummy data using API endpoints to verify APIs are working';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== Starting Database API Seeder ===");

        // 1. Wipe & fresh migrate the database
        $this->info("Wiping and fresh migrating database...");
        Artisan::call('migrate:fresh', [], $this->output);
        $this->info("Database wiped and migrated successfully.");

        // 2. Seed basic Admin user and metadata/lookups directly in DB
        $this->info("Seeding Admin user and config/lookup tables directly...");
        
        $admin = \App\Models\User::create([
            'name' => 'Armando Turcotte',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
            'active' => 1,
            'email_verified_at' => now(),
        ]);

        // Seed Lead Statuses
        DB::table('lead_statuses')->insert([
            ['name' => 'New', 'color' => '#6366f1', 'order_num' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Contacted', 'color' => '#0ea5e9', 'order_num' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Working', 'color' => '#f59e0b', 'order_num' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Qualified', 'color' => '#10b981', 'order_num' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Proposal Sent', 'color' => '#8b5cf6', 'order_num' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Customer', 'color' => '#22c55e', 'order_num' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lost', 'color' => '#ef4444', 'order_num' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Lead Sources
        DB::table('lead_sources')->insert([
            ['name' => 'Website', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Phone', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Email', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Referral', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Social Media', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Expense Categories
        DB::table('expense_categories')->insert([
            ['name' => 'Hosting & Cloud Servers', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Software Licenses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Office Supplies', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Travel & Commute', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Contract Types
        DB::table('contract_types')->insert([
            ['name' => 'NDA Agreement', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Service Level Agreement (SLA)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Software License Agreement', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Ticket Departments
        DB::table('ticket_departments')->insert([
            ['name' => 'Technical Support', 'description' => 'Server, API, and technical glitches support', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Billing & Finance', 'description' => 'Invoices, payments, and billing inquiries', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed KB Categories
        DB::table('kb_categories')->insert([
            ['name' => 'Sales', 'slug' => 'sales', 'color' => '#10b981', 'description' => 'Sales articles', 'order_num' => 1, 'disabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Info', 'slug' => 'info', 'color' => '#3b82f6', 'description' => 'General info', 'order_num' => 2, 'disabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Support', 'slug' => 'support', 'color' => '#8b5cf6', 'description' => 'Technical Support', 'order_num' => 3, 'disabled' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->info("Basic config/lookup data seeded successfully.");

        // 3. Login via API
        $this->info("Logging in via API auth/login...");
        $loginResponse = $this->dispatchRequest('POST', '/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'admin',
        ]);

        if ($loginResponse->getStatusCode() !== 200) {
            $this->error("Auth API Failed! Status: " . $loginResponse->getStatusCode());
            $this->error("Response: " . $loginResponse->getContent());
            return 1;
        }

        $loginJson = json_decode($loginResponse->getContent(), true);
        $token = $loginJson['token'];
        $this->info("Authenticated successfully. Sanctum token: " . substr($token, 0, 12) . "...");

        // 4. Seeding other entities via API
        $this->info("Seeding operational dummy data via API endpoints...");

        // A. Seed Roles
        $roleId = $this->seedRole($token);
        if (!$roleId) return 1;

        // B. Seed Staff Members
        $staffId = $this->seedStaff($token, $roleId);
        if (!$staffId) return 1;

        // C. Seed Clients & primary contacts
        $clientId = $this->seedClient($token);
        if (!$clientId) return 1;

        // D. Seed additional Contact
        $contactId = $this->seedContact($token, $clientId);
        if (!$contactId) return 1;

        // E. Seed Leads
        $leadId = $this->seedLead($token, $admin->id);
        if (!$leadId) return 1;

        // F. Seed Predefined Items
        $predefItemId = $this->seedPredefinedItem($token);
        if (!$predefItemId) return 1;

        // G. Seed Projects
        $projectId = $this->seedProject($token, $clientId, $admin->id, $staffId);
        if (!$projectId) return 1;

        // H. Seed Tasks
        $taskId = $this->seedTask($token, $projectId, $admin->id, $clientId);
        if (!$taskId) return 1;

        // I. Seed Invoices
        $invoiceId = $this->seedInvoice($token, $clientId, $projectId);
        if (!$invoiceId) return 1;

        // J. Seed Payments
        $paymentId = $this->seedPayment($token, $invoiceId);
        if (!$paymentId) return 1;

        // K. Seed Credit Notes
        $creditNoteId = $this->seedCreditNote($token, $clientId);
        if (!$creditNoteId) return 1;

        // L. Seed Subscriptions
        $subscriptionId = $this->seedSubscription($token, $clientId);
        if (!$subscriptionId) return 1;

        // M. Seed Announcements
        $announcementId = $this->seedAnnouncement($token);
        if (!$announcementId) return 1;

        // N. Seed Goals
        $goalId = $this->seedGoal($token);
        if (!$goalId) return 1;

        // O. Seed Expenses
        $expenseId = $this->seedExpense($token, $clientId);
        if (!$expenseId) return 1;

        // P. Seed Contracts
        $contractId = $this->seedContract($token, $clientId);
        if (!$contractId) return 1;

        // Q. Seed Tickets
        $ticketId = $this->seedTicket($token, $clientId, $admin->id);
        if (!$ticketId) return 1;

        // R. Seed Estimate Requests
        $estimateRequestId = $this->seedEstimateRequest($token);
        if (!$estimateRequestId) return 1;

        // S. Seed Estimate Request Forms
        $formId = $this->seedEstimateRequestForm($token, $admin->id);
        if (!$formId) return 1;

        // T. Seed KB Articles
        $kbArticleId = $this->seedKbArticle($token);
        if (!$kbArticleId) return 1;

        // U. Seed Surveys
        $surveyId = $this->seedSurvey($token);
        if (!$surveyId) return 1;

        // V. Seed Mail Lists
        $mailListId = $this->seedMailList($token);
        if (!$mailListId) return 1;

        $this->info("=== Database successfully cleared and seeded via APIs with 0 errors! ===");
        return 0;
    }

    /**
     * Dispatch an internal request to the Laravel HTTP application kernel.
     */
    protected function dispatchRequest(string $method, string $uri, array $data = [], string $token = null)
    {
        // Re-create HTTP Request
        $request = Request::create($uri, $method, $data);
        $request->headers->set('Accept', 'application/json');
        if ($token) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        // Rebind current request in Laravel container
        app()->instance('request', $request);

        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        return $response;
    }

    /**
     * Seeding functions utilizing API POST endpoints
     */
    protected function seedRole($token)
    {
        $this->info("API POST: Creating Role...");
        $response = $this->dispatchRequest('POST', '/api/roles', [
            'name' => 'Sales Representative',
            'slug' => 'sales-rep',
            'description' => 'Handles leads and client contracts',
            'permissions' => ['leads' => ['view', 'create', 'edit'], 'clients' => ['view']],
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Role! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedStaff($token, $roleId)
    {
        $this->info("API POST: Creating Staff member...");
        $response = $this->dispatchRequest('POST', '/api/staff', [
            'name' => 'Elias Konopelski',
            'email' => 'elias@company.com',
            'password' => 'Admin1234!',
            'password_confirmation' => 'Admin1234!',
            'role_id' => $roleId,
            'phone' => '+1-555-0101',
            'department' => 'Sales',
            'active' => true,
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Staff member! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedClient($token)
    {
        $this->info("API POST: Creating Client...");
        $response = $this->dispatchRequest('POST', '/api/clients', [
            'company' => 'Nader-Abernathy',
            'vat' => 'NI712345',
            'phonenumber' => '+1-650-555-0101',
            'website' => 'https://nader.example.com',
            'address' => '123 Business Park',
            'city' => 'New York',
            'country' => 'United States',
            'contact_firstname' => 'James',
            'contact_lastname' => 'Smith',
            'contact_email' => 'james.smith@nader.com',
            'contact_phone' => '+1-650-555-0101',
            'contact_title' => 'CEO',
            'contact_password' => 'Admin1234!',
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Client! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedContact($token, $clientId)
    {
        $this->info("API POST: Creating extra Contact...");
        $response = $this->dispatchRequest('POST', '/api/contacts', [
            'client_id' => $clientId,
            'firstname' => 'Sarah',
            'lastname' => 'Conner',
            'email' => 'sarah.conner@nader.com',
            'phonenumber' => '+1-650-555-0102',
            'title' => 'VP Operations',
            'active' => true,
        ], $token);

        if ($response->getStatusCode() !== 201 && $response->getStatusCode() !== 200) {
            $this->error("Failed to create Contact! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'] ?? $res['contact']['id'] ?? 1;
    }

    protected function seedLead($token, $adminId)
    {
        $this->info("API POST: Creating Lead...");
        $statusId = DB::table('lead_statuses')->first()->id;
        $sourceId = DB::table('lead_sources')->first()->id;

        $response = $this->dispatchRequest('POST', '/api/leads', [
            'name' => 'Alice Wonderland',
            'company' => 'Wonder Solutions',
            'email' => 'alice@leads.example.com',
            'phonenumber' => '+1-555-0200',
            'status_id' => $statusId,
            'source_id' => $sourceId,
            'lead_value' => 15000,
            'city' => 'New York',
            'country' => 'United States',
            'assigned_id' => $adminId,
            'description' => 'Interested in our CRM extensions.',
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Lead! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedPredefinedItem($token)
    {
        $this->info("API POST: Creating Predefined Item...");
        $response = $this->dispatchRequest('POST', '/api/predefined-items', [
            'name' => 'WordPress Development',
            'description' => 'Custom theme development services',
            'rate' => 120.00,
            'tax_rate' => 10.00,
            'unit' => 'Hour',
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Predefined Item! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedProject($token, $clientId, $adminId, $staffId)
    {
        $this->info("API POST: Creating Project...");
        $response = $this->dispatchRequest('POST', '/api/projects', [
            'name' => 'CRM Expansion Project',
            'description' => 'Migrate database and implement standard billing systems.',
            'client_id' => $clientId,
            'billing_type' => 'Fixed Rate',
            'budget' => 25000.00,
            'start_date' => now()->toDateString(),
            'deadline' => now()->addDays(60)->toDateString(),
            'status' => 'In Progress',
            'member_ids' => [$adminId, $staffId],
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Project! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedTask($token, $projectId, $adminId, $clientId)
    {
        $this->info("API POST: Creating Task...");
        $response = $this->dispatchRequest('POST', '/api/tasks', [
            'name' => 'Define Database Schema',
            'description' => 'Define tables and fields layout',
            'priority' => 'High',
            'status' => 'In Progress',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'assigned_to' => $adminId,
            'related_to_type' => 'project',
            'related_to_id' => $projectId,
            'client_id' => $clientId,
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Task! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedInvoice($token, $clientId, $projectId)
    {
        $this->info("API POST: Creating Invoice...");
        $response = $this->dispatchRequest('POST', '/api/invoices', [
            'client_id' => $clientId,
            'project_id' => $projectId,
            'status' => 'unpaid',
            'date' => now()->toDateString(),
            'duedate' => now()->addDays(30)->toDateString(),
            'subtotal' => 240.00,
            'tax' => 24.00,
            'total' => 264.00,
            'tags' => 'design,development',
            'items' => [
                [
                    'description' => 'Web Development',
                    'long_description' => 'Figma review & initial setup',
                    'qty' => 2,
                    'rate' => 120.00,
                    'tax_rate' => 10.00,
                ]
            ],
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Invoice! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedPayment($token, $invoiceId)
    {
        $this->info("API POST: Creating Payment...");
        $response = $this->dispatchRequest('POST', '/api/payments', [
            'invoice_id' => $invoiceId,
            'amount' => 100.00,
            'paymentmode' => 'Stripe',
            'date' => now()->toDateString(),
            'transactionid' => 'TXN-API-TEST',
            'note' => 'Partial payment via Stripe',
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Payment! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedCreditNote($token, $clientId)
    {
        $this->info("API POST: Creating Credit Note...");
        $response = $this->dispatchRequest('POST', '/api/credit-notes', [
            'client_id' => $clientId,
            'amount' => 500.00,
            'date' => now()->toDateString(),
            'status' => 'Open',
            'reference' => 'REF-API-1',
            'admin_note' => 'Credit note generated via API',
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Credit Note! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedSubscription($token, $clientId)
    {
        $this->info("API POST: Creating Subscription...");
        $response = $this->dispatchRequest('POST', '/api/subscriptions', [
            'name' => 'Monthly Premium SLA Support',
            'client_id' => $clientId,
            'amount' => 299.00,
            'billing_period' => 'monthly',
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'stripe_plan' => 'price_prem_support_1',
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Subscription! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedAnnouncement($token)
    {
        $this->info("API POST: Creating Announcement...");
        $response = $this->dispatchRequest('POST', '/api/announcements', [
            'subject' => 'Welcome to our Customer Portal',
            'message' => 'Please complete your client profile information.',
            'show_to_staff' => true,
            'show_to_clients' => true,
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Announcement! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedGoal($token)
    {
        $this->info("API POST: Creating Goal...");
        $response = $this->dispatchRequest('POST', '/api/goals', [
            'subject' => 'Q3 Sales Targets',
            'goal_type' => 'invoice_amount',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'target_value' => 80000.00,
            'current_value' => 0.00,
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Goal! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedExpense($token, $clientId)
    {
        $this->info("API POST: Creating Expense...");
        $categoryId = DB::table('expense_categories')->first()->id;

        $response = $this->dispatchRequest('POST', '/api/expenses', [
            'name' => 'Figma Professional Team Package',
            'amount' => 75.00,
            'date' => now()->toDateString(),
            'category_id' => $categoryId,
            'client_id' => $clientId,
            'reference' => 'FIG-API-2026',
            'payment_mode' => 'Credit Card',
            'status' => 'unbilled',
            'billable' => true,
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Expense! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedContract($token, $clientId)
    {
        $this->info("API POST: Creating Contract...");
        $contractTypeId = DB::table('contract_types')->first()->id;

        $response = $this->dispatchRequest('POST', '/api/contracts', [
            'subject' => 'SLA Technical Support retainership',
            'client_id' => $clientId,
            'contract_type_id' => $contractTypeId,
            'value' => 4500.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(90)->toDateString(),
            'status' => 'Active',
            'description' => 'Terms of developer support retainership',
            'signed' => true,
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Contract! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedTicket($token, $clientId, $adminId)
    {
        $this->info("API POST: Creating Support Ticket...");
        $departmentId = DB::table('ticket_departments')->first()->id;

        $response = $this->dispatchRequest('POST', '/api/tickets', [
            'subject' => 'Cannot upload attachments on vault entries',
            'client_id' => $clientId,
            'priority' => 'High',
            'status' => 'Open',
            'department_id' => $departmentId,
            'message' => 'Getting a permission error when attempting file uploads.',
            'assigned_to' => $adminId,
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Ticket! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedEstimateRequest($token)
    {
        $this->info("API POST: Creating Estimate Request...");
        $response = $this->dispatchRequest('POST', '/api/estimate-requests', [
            'name' => 'Alice Green',
            'email' => 'alice@green.example.com',
            'message' => 'Quote query for mobile ecommerce app developer support.',
            'status' => 'pending',
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Estimate Request! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedEstimateRequestForm($token, $adminId)
    {
        $this->info("API POST: Creating Estimate Request Form...");
        $response = $this->dispatchRequest('POST', '/api/estimate-request-forms', [
            'name' => 'Web Development Quote Request Form',
            'email' => 'web-quotes@perfexcrm.io',
            'tags' => 'web,estimates,api',
            'assigned_to' => $adminId,
            'status' => 'active',
            'language' => 'English',
            'recaptcha_enabled' => false,
            'submit_btn_text' => 'Submit Quote Request',
            'submit_btn_bg_color' => '#84c529',
            'submission_action' => 'message',
            'submission_message' => 'Thank you for your submission.',
            'notify_enabled' => true,
            'notify_type' => 'specific',
            'notify_staff_ids' => (string)$adminId,
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Estimate Request Form! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedKbArticle($token)
    {
        $this->info("API POST: Creating KB Article...");
        $categoryId = DB::table('kb_categories')->first()->id;

        $response = $this->dispatchRequest('POST', '/api/kb-articles', [
            'title' => 'How to setup credentials in Vault',
            'content' => 'This guide walks you through secure credentials storage and sharing settings within the client vault panel.',
            'category_id' => $categoryId,
            'status' => 'published',
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create KB Article! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['id'];
    }

    protected function seedSurvey($token)
    {
        $this->info("API POST: Creating Survey...");
        $response = $this->dispatchRequest('POST', '/api/surveys', [
            'subject' => 'Client Satisfaction Survey 2026',
            'view_description' => 'Satisfaction survey view details',
            'active' => true,
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Survey! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['survey']['id'] ?? null;
    }

    protected function seedMailList($token)
    {
        $this->info("API POST: Creating Mail List...");
        $response = $this->dispatchRequest('POST', '/api/mail-lists', [
            'name' => 'API Newsletter Subscribers',
        ], $token);

        if ($response->getStatusCode() !== 201) {
            $this->error("Failed to create Mail List! Status: " . $response->getStatusCode() . " Response: " . $response->getContent());
            return null;
        }

        $res = json_decode($response->getContent(), true);
        return $res['list']['id'] ?? null;
    }
}
