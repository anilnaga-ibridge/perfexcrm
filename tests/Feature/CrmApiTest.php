<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\DemoDataSeeder;

class CrmApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test all primary API endpoints to ensure they are online and responding with correct data structure.
     */
    public function test_crm_api_endpoints(): void
    {
        // Run database seeders
        $this->seed(DemoDataSeeder::class);

        // 1. Get the admin user we seeded
        $admin = User::where('email', 'admin@test.com')->first();
        if (!$admin) {
            $admin = User::factory()->create([
                'name' => 'Armando Turcotte',
                'email' => 'admin@test.com',
                'role' => 'admin',
            ]);
        }

        // 2. Test Dashboard Metrics API
        $response = $this->actingAs($admin)
            ->getJson('/api/dashboard-metrics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_leads',
                'converted_leads',
                'leads_overview',
                'total_clients',
                'invoices_awaiting_payment',
                'total_invoices',
                'invoice_overview',
                'outstanding_amount',
                'past_due_amount',
                'paid_amount',
                'projects_in_progress',
                'tasks_not_finished',
                'todo_items',
            ]);

        // 3. Test Clients API
        $response = $this->actingAs($admin)
            ->getJson('/api/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'clients' => [
                    'data',
                    'current_page',
                    'last_page',
                    'total',
                ]
            ]);

        // 4. Test Leads API
        $response = $this->actingAs($admin)
            ->getJson('/api/leads');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'last_page',
                'total',
            ]);

        // 5. Test Invoices API
        $response = $this->actingAs($admin)
            ->getJson('/api/invoices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'invoices' => [
                    'data',
                    'current_page',
                    'last_page',
                    'total',
                ],
                'stats' => [
                    'total',
                    'draft',
                    'unpaid',
                    'paid',
                    'overdue',
                    'partially_paid',
                    'total_amount',
                    'unpaid_amount',
                    'paid_amount',
                ]
            ]);

        // 6. Test Lead Metadata API
        $response = $this->actingAs($admin)
            ->getJson('/api/lead-metadata');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'statuses',
                'sources',
                'staff',
            ]);

        // 7. Test Staff API
        $response = $this->actingAs($admin)
            ->getJson('/api/staff');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'staff' => [
                    'data',
                    'current_page',
                    'last_page',
                    'total',
                ]
            ]);

        // 8. Test Payments API
        $response = $this->actingAs($admin)->getJson('/api/payments');
        $response->assertStatus(200);

        // 9. Test Credit Notes API
        $response = $this->actingAs($admin)->getJson('/api/credit-notes');
        $response->assertStatus(200);

        // 10. Test Predefined Items API
        $response = $this->actingAs($admin)->getJson('/api/predefined-items');
        $response->assertStatus(200);

        // 11. Test Subscriptions API
        $response = $this->actingAs($admin)->getJson('/api/subscriptions');
        $response->assertStatus(200);

        // 12. Test Tasks API
        $response = $this->actingAs($admin)->getJson('/api/tasks');
        $response->assertStatus(200);

        // 13. Test Announcements API
        $response = $this->actingAs($admin)->getJson('/api/announcements');
        $response->assertStatus(200);

        // 14. Test Goals API
        $response = $this->actingAs($admin)->getJson('/api/goals');
        $response->assertStatus(200);

        // 15. Test Media API
        $response = $this->actingAs($admin)->getJson('/api/media');
        $response->assertStatus(200);

        // 16. Test Expenses API
        $response = $this->actingAs($admin)->getJson('/api/expenses');
        $response->assertStatus(200);

        // 17. Test Projects API
        $response = $this->actingAs($admin)->getJson('/api/projects');
        $response->assertStatus(200);

        // 18. Test Contracts API
        $response = $this->actingAs($admin)->getJson('/api/contracts');
        $response->assertStatus(200);

        // 19. Test Tickets API
        $response = $this->actingAs($admin)->getJson('/api/tickets');
        $response->assertStatus(200);

        // 20. Test Estimate Requests API
        $response = $this->actingAs($admin)->getJson('/api/estimate-requests');
        $response->assertStatus(200);

        // 21. Test KB Articles API
        $response = $this->actingAs($admin)->getJson('/api/kb-articles');
        $response->assertStatus(200);

        // 22. Test Reports APIs
        $response = $this->actingAs($admin)->getJson('/api/reports/sales');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->getJson('/api/reports/expenses');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->getJson('/api/reports/finance');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->getJson('/api/reports/team');
        $response->assertStatus(200);
    }
}
