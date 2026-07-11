<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ModuleSsoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::firstOrCreate(['name' => 'Administrator', 'slug' => 'admin']);
    }

    public function test_generate_sso_url(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($user);
        \Laravel\Sanctum\Sanctum::actingAs($user);
        $response = $this->getJson('/api/modules/sso-url?redirect=/modules/hrm/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure(['url']);

        $url = $response->json('url');
        $this->assertStringContainsString('/plugins/sso?token=', $url);
    }

    public function test_successful_sso_login(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($user);
        \Laravel\Sanctum\Sanctum::actingAs($user);
        $response = $this->getJson('/api/modules/sso-url?redirect=/modules/hrm/dashboard');

        $url = $response->json('url');

        // Extract token from query params
        $queryParams = [];
        parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'] ?? null;

        $this->assertNotNull($token);

        // Make web request to the SSO login endpoint
        $loginResponse = $this->get($url);

        $loginResponse->assertRedirect('/modules/hrm/dashboard');

        // Assert user is authenticated via web guard
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_invalid_sso_token(): void
    {
        $response = $this->get('/modules/sso?token=invalid_token');

        $response->assertStatus(403);
    }

    public function test_expired_sso_token(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($user);
        \Laravel\Sanctum\Sanctum::actingAs($user);
        $response = $this->getJson('/api/modules/sso-url?redirect=/modules/hrm/dashboard');

        $url = $response->json('url');

        // Flush cache to simulate expiration
        Cache::flush();

        $loginResponse = $this->get($url);

        $loginResponse->assertStatus(403);
    }

    public function test_sso_open_redirect_protection(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        // Malicious redirect path trying to escape modules context
        $this->actingAs($user);
        \Laravel\Sanctum\Sanctum::actingAs($user);
        $response = $this->getJson('/api/modules/sso-url?redirect=http://malicious.com/escape');

        $url = $response->json('url');

        $loginResponse = $this->get($url);

        $loginResponse->assertStatus(403);
    }
}
