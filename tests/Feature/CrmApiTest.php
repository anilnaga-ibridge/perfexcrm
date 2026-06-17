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

        // 19b. Test Tickets Metadata API
        $response = $this->actingAs($admin)->getJson('/api/tickets/metadata');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'departments',
                'staff',
                'clients',
                'contacts',
                'projects',
                'services',
            ]);

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

        // 23. Test Client Files API
        $response = $this->actingAs($admin)->getJson('/api/clients/1/files');
        $response->assertStatus(200);

        // Upload a mock file
        $file = \Illuminate\Http\UploadedFile::fake()->create('test_attachment.pdf', 100);
        $response = $this->actingAs($admin)->postJson('/api/clients/1/files', [
            'file' => $file,
        ]);
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'client_id',
                'file_name',
                'file_path',
                'file_size',
                'visible_to_customer',
            ]);

        $fileId = $response->json('id');

        // Toggle visibility
        $response = $this->actingAs($admin)->putJson("/api/client-files/{$fileId}/status", [
            'visible_to_customer' => true,
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'visible_to_customer' => true,
            ]);

        // Delete client file
        $response = $this->actingAs($admin)->deleteJson("/api/client-files/{$fileId}");
        $response->assertStatus(200);
    }

    public function test_client_vault_endpoints(): void
    {
        $this->seed(DemoDataSeeder::class);

        $admin = User::where('email', 'admin@test.com')->first();
        $staff = User::where('email', 'elias@company.com')->first(); // role: staff
        $otherStaff = User::where('email', 'tamara@company.com')->first(); // role: staff

        // 1. Create a vault entry visible to all staff
        $response = $this->actingAs($admin)->postJson('/api/clients/1/vaults', [
            'server_address' => '192.168.1.1',
            'port' => 22,
            'username' => 'admin_user',
            'password' => 'secret_pass_123',
            'short_description' => 'Visible to all staff',
            'visibility' => 'all_staff',
            'share_in_projects' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'client_id',
                'server_address',
                'port',
                'username',
                'short_description',
                'visibility',
                'share_in_projects',
                'created_by',
            ]);

        $allStaffEntryId = $response->json('id');

        // 2. Create a vault entry visible only to creator (admins not excluded)
        $response = $this->actingAs($staff)->postJson('/api/clients/1/vaults', [
            'server_address' => '192.168.1.2',
            'port' => 80,
            'username' => 'staff_user',
            'password' => 'staff_secret_456',
            'short_description' => 'Me only',
            'visibility' => 'me',
            'share_in_projects' => false,
        ]);
        $response->assertStatus(201);
        $meEntryId = $response->json('id');

        // 3. Create a vault entry visible only to admins
        $response = $this->actingAs($admin)->postJson('/api/clients/1/vaults', [
            'server_address' => '192.168.1.3',
            'port' => 443,
            'username' => 'sysadmin',
            'password' => 'admin_secret_789',
            'short_description' => 'Admins only',
            'visibility' => 'admins',
            'share_in_projects' => false,
        ]);
        $response->assertStatus(201);
        $adminEntryId = $response->json('id');

        // 4. Test listing for Admin (should see all 3 entries)
        $response = $this->actingAs($admin)->getJson('/api/clients/1/vaults');
        $response->assertStatus(200);
        $this->assertCount(3, $response->json());

        // 5. Test listing for creator staff (should see all_staff and their own 'me' entries, but NOT the 'admins' entry)
        $response = $this->actingAs($staff)->getJson('/api/clients/1/vaults');
        $response->assertStatus(200);
        $ids = collect($response->json())->pluck('id')->all();
        $this->assertContains($allStaffEntryId, $ids);
        $this->assertContains($meEntryId, $ids);
        $this->assertNotContains($adminEntryId, $ids);

        // 6. Test listing for another staff member (should see 'all_staff' entry, but NOT the other's 'me' entry and NOT 'admins' entry)
        $response = $this->actingAs($otherStaff)->getJson('/api/clients/1/vaults');
        $response->assertStatus(200);
        $ids = collect($response->json())->pluck('id')->all();
        $this->assertContains($allStaffEntryId, $ids);
        $this->assertNotContains($meEntryId, $ids);
        $this->assertNotContains($adminEntryId, $ids);

        // 7. Test Decrypt endpoint accessibility
        // Admin can decrypt all staff entry
        $response = $this->actingAs($admin)->getJson("/api/vault-entries/{$allStaffEntryId}/decrypt");
        $response->assertStatus(200)
            ->assertJson(['password' => 'secret_pass_123']);

        // Creator staff can decrypt their own 'me' entry
        $response = $this->actingAs($staff)->getJson("/api/vault-entries/{$meEntryId}/decrypt");
        $response->assertStatus(200)
            ->assertJson(['password' => 'staff_secret_456']);

        // Non-creator staff CANNOT decrypt another's 'me' entry
        $response = $this->actingAs($otherStaff)->getJson("/api/vault-entries/{$meEntryId}/decrypt");
        $response->assertStatus(403);

        // Non-creator staff CAN decrypt 'all_staff' entry
        $response = $this->actingAs($otherStaff)->getJson("/api/vault-entries/{$allStaffEntryId}/decrypt");
        $response->assertStatus(200);

        // 8. Test update action and permission
        // Other staff cannot update staff's entry
        $response = $this->actingAs($otherStaff)->putJson("/api/vault-entries/{$meEntryId}", [
            'server_address' => '192.168.1.9',
            'port' => 80,
            'username' => 'hacker',
            'password' => 'hacked_123',
            'visibility' => 'me',
        ]);
        $response->assertStatus(403);

        // Creator can update their own entry
        $response = $this->actingAs($staff)->putJson("/api/vault-entries/{$meEntryId}", [
            'server_address' => '192.168.1.20',
            'port' => 8080,
            'username' => 'staff_user_updated',
            'password' => 'staff_new_secret',
            'visibility' => 'me',
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'server_address' => '192.168.1.20',
                'username' => 'staff_user_updated',
            ]);

        // Decrypt updated password
        $response = $this->actingAs($staff)->getJson("/api/vault-entries/{$meEntryId}/decrypt");
        $response->assertStatus(200)
            ->assertJson(['password' => 'staff_new_secret']);

        // 9. Test Delete permission
        // Other staff cannot delete staff's entry
        $response = $this->actingAs($otherStaff)->deleteJson("/api/vault-entries/{$meEntryId}");
        $response->assertStatus(403);

        // Creator can delete their own entry
        $response = $this->actingAs($staff)->deleteJson("/api/vault-entries/{$meEntryId}");
        $response->assertStatus(200);

        // Admin can delete any entry
        $response = $this->actingAs($admin)->deleteJson("/api/vault-entries/{$allStaffEntryId}");
        $response->assertStatus(200);
    }
}
