<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\EmailMailerConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailMailerConfigurationController extends Controller
{
    public function show(): JsonResponse
    {
        $configuration = EmailMailerConfiguration::find(1);

        return response()->json([
            'data' => $this->serialize($configuration),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mail_engine' => ['required', 'in:PHPMailer,CodeIgniter'],
            'email_protocol' => ['required', 'in:SMTP,Microsoft OAuth 2.0,Gmail OAuth 2.0,Sendmail,Mail'],
            'email_encryption' => ['required', 'in:None,SSL,TLS'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'between:1,65535'],
            'from_email' => ['nullable', 'email:rfc', 'max:255'],
            'smtp_user' => ['nullable', 'string', 'max:255'],
            'smtp_pass' => ['nullable', 'string', 'max:2048'],
            'clear_smtp_password' => ['nullable', 'boolean'],
            'email_charset' => ['required', 'string', 'max:40'],
            'bcc_emails' => ['nullable', 'string', 'max:2000'],
        ]);

        $configuration = EmailMailerConfiguration::firstOrNew(['id' => 1]);
        $configuration->fill([
            'mail_engine' => $data['mail_engine'],
            'protocol' => $data['email_protocol'],
            'encryption' => $data['email_encryption'],
            'smtp_host' => $data['smtp_host'] ?? null,
            'smtp_port' => $data['smtp_port'] ?? null,
            'from_email' => $data['from_email'] ?? null,
            'smtp_username' => $data['smtp_user'] ?? null,
            'charset' => $data['email_charset'],
            'bcc_emails' => $data['bcc_emails'] ?? null,
        ]);

        // A blank password means "keep the existing secret". Clearing it is
        // explicit so an unchanged form can never erase the stored credential.
        if (!empty($data['clear_smtp_password'])) {
            $configuration->smtp_password = null;
        } elseif (array_key_exists('smtp_pass', $data) && $data['smtp_pass'] !== '') {
            $configuration->smtp_password = $data['smtp_pass'];
        }

        $configuration->save();
        ActivityLog::log('Updated secure SMTP mailer configuration');

        return response()->json([
            'message' => 'SMTP settings saved securely.',
            'data' => $this->serialize($configuration),
        ]);
    }

    private function serialize(?EmailMailerConfiguration $configuration): array
    {
        if (!$configuration) {
            return [
                'mail_engine' => 'PHPMailer',
                'email_protocol' => 'SMTP',
                'email_encryption' => 'TLS',
                'smtp_host' => null,
                'smtp_port' => 587,
                'from_email' => null,
                'smtp_user' => null,
                'smtp_password_set' => false,
                'email_charset' => 'utf-8',
                'bcc_emails' => null,
            ];
        }

        return [
            'mail_engine' => $configuration->mail_engine,
            'email_protocol' => $configuration->protocol,
            'email_encryption' => $configuration->encryption,
            'smtp_host' => $configuration->smtp_host,
            'smtp_port' => $configuration->smtp_port,
            'from_email' => $configuration->from_email,
            'smtp_user' => $configuration->smtp_username,
            'smtp_password_set' => !empty($configuration->smtp_password),
            'email_charset' => $configuration->charset,
            'bcc_emails' => $configuration->bcc_emails,
        ];
    }
}
