<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmailMailerConfigurationTest extends TestCase
{
    use RefreshDatabase;

    private function settingsUser(): User
    {
        $role = Role::create([
            'name' => 'Mail Settings Administrator',
            'slug' => 'mail-settings-admin',
            'permissions' => ['Settings' => ['view_global' => true, 'edit' => true]],
        ]);

        return User::create([
            'name' => 'Mail Settings Admin',
            'email' => 'mail-settings-admin@example.test',
            'password' => bcrypt('Secret123!'),
            'role_id' => $role->id,
            'role' => $role->slug,
            'active' => true,
        ]);
    }

    public function test_smtp_password_is_encrypted_and_never_exposed_by_the_api(): void
    {
        $user = $this->settingsUser();
        $secret = 'smtp-secret-value';

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/email-mailer-configuration', [
            'mail_engine' => 'PHPMailer',
            'email_protocol' => 'SMTP',
            'email_encryption' => 'TLS',
            'smtp_host' => 'smtp.example.test',
            'smtp_port' => 587,
            'from_email' => 'mailer@example.test',
            'smtp_user' => 'mailer-user',
            'smtp_pass' => $secret,
            'email_charset' => 'utf-8',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.smtp_password_set', true)
            ->assertJsonMissingPath('data.smtp_pass')
            ->assertJsonMissingPath('data.smtp_password');

        $storedRow = DB::table('email_mailer_configurations')->first(['smtp_username', 'smtp_password']);
        $this->assertNotSame('mailer-user', $storedRow->smtp_username);
        $storedPassword = $storedRow->smtp_password;
        $this->assertNotSame($secret, $storedPassword);

        $this->actingAs($user, 'sanctum')->getJson('/api/email-mailer-configuration')
            ->assertOk()
            ->assertJsonPath('data.smtp_password_set', true)
            ->assertJsonMissingPath('data.smtp_pass')
            ->assertJsonMissingPath('data.smtp_password');
    }
}
