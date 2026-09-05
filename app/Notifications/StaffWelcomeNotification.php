<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class StaffWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $token = Password::createToken($notifiable);
        $email = method_exists($notifiable, 'getEmailForPasswordReset') 
            ? $notifiable->getEmailForPasswordReset() 
            : $notifiable->email;

        $resetUrl = url('/admin/reset-password/' . $token . '?email=' . urlencode($email));
        $loginUrl = url('/login');
        
        $roleName = 'Employee';
        if ($notifiable->relationLoaded('role') && $notifiable->role) {
            $roleName = $notifiable->role->name ?? ucfirst($notifiable->role->slug ?? 'employee');
        } elseif ($notifiable->role_id) {
            $role = \App\Models\Role::find($notifiable->role_id);
            if ($role) {
                $roleName = $role->name ?? ucfirst($role->slug);
            }
        }

        $appName = (config('app.name') && config('app.name') !== 'Laravel') ? config('app.name') : 'iBRIDGE CRM';

        $logoUrl = asset('images/logo.png');
        $companyLogoHtml = '<img src="' . $logoUrl . '" alt="' . htmlspecialchars($appName) . '" style="max-height:50px; width:auto; display:inline-block; vertical-align:middle; border:0;" />';

        $replacements = [
            '##COMPANY_LOGO##'   => $companyLogoHtml,
            '{company_logo}'     => $companyLogoHtml,
            '##EMPLOYEE_NAME##'   => $notifiable->name,
            '##COMPANY_NAME##'    => $appName,
            '##ROLE_NAME##'       => $roleName,
            '##SET_PASSWORD_URL##'=> $resetUrl,
            '##LOGIN_URL##'       => $loginUrl,
            '##STAFF_EMAIL##'     => $email,
            '##CONTRACT_NAME##'   => '',
            '##LEAVE_TYPE##'       => '',
            '##REASON##'          => '',
            '##STATUS##'          => 'Active',
            '##CONTRACT_VALUE##'  => '',
            '##START_DATE##'      => date('Y-m-d'),
            '##EXPENSE_AMOUNT##'  => '',
            '##CLIENT_NAME##'     => $notifiable->name,
            '##EXPIRY_DATE##'     => '',
            '##END_DATE##'        => '',
            '{employee_name}'     => $notifiable->name,
            '{company_name}'      => $appName,
            '{role_name}'         => $roleName,
            '{set_password_url}'  => $resetUrl,
            '{login_url}'         => $loginUrl,
            '{staff_email}'       => $email,
        ];

        // Query database for custom template configured by admin in Email Templates Studio
        $dbTemplate = \App\Models\EmailTemplate::where('key', 'welcome_staff')
            ->orWhere('key', '203')
            ->orWhere('name', 'like', '%Employee Welcome Mail%')
            ->first();

        $subject = "Welcome to {$appName}!";
        if ($dbTemplate && !empty($dbTemplate->subject)) {
            $subject = strtr($dbTemplate->subject, $replacements);
        }

        if ($dbTemplate && !empty($dbTemplate->body)) {
            $htmlContent = strtr($dbTemplate->body, $replacements);
        } else {
            $templateBody = '
            <div style="font-family:\'Outfit\',\'Inter\',sans-serif; max-width:580px; margin:0 auto; background:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 10px 25px rgba(0,0,0,0.05);">
                <div style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); padding:32px 24px; text-align:center; color:#ffffff;">
                    <h1 style="margin:0; font-size:22px; font-weight:800;">##COMPANY_NAME##</h1>
                    <p style="margin:6px 0 0 0; font-size:13px; opacity:0.9;">Staff Account Activation</p>
                </div>
                <div style="padding:32px;">
                    <h2 style="font-size:18px; color:#1e293b; margin:0 0 12px 0;">Welcome aboard, ##EMPLOYEE_NAME##! 🎉</h2>
                    <p style="font-size:14px; color:#475569; line-height:1.6;">An account has been created for your email: <strong>##STAFF_EMAIL##</strong> assigned to role <strong>##ROLE_NAME##</strong>.</p>
                    <div style="background:#f0fdf4; border-left:4px solid #16a34a; padding:14px 18px; border-radius:6px; font-size:13px; color:#166534; margin:24px 0;">
                        🔒 For security reasons, please click the button below to set up your password and activate access.
                    </div>
                    <div style="text-align:center; margin:28px 0;">
                        <a href="##SET_PASSWORD_URL##" target="_blank" style="display:inline-block; background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); color:#ffffff; font-size:15px; font-weight:700; text-decoration:none; padding:14px 36px; border-radius:99px; box-shadow:0 8px 20px rgba(124,58,237,0.3);">Set Your Password &rarr;</a>
                    </div>
                    <div style="font-size:12px; color:#94a3b8; border-top:1px solid #f1f5f9; padding-top:16px;">
                        <p style="margin:0 0 4px 0;"><strong>Security Reminder:</strong></p>
                        <ul style="margin:0; padding-left:18px;">
                            <li>This password setup link will expire in 60 minutes.</li>
                            <li>Never share your credentials or link with anyone.</li>
                        </ul>
                    </div>
                </div>
                <div style="background:#f8fafc; padding:20px 32px; text-align:center; border-top:1px solid #e2e8f0; font-size:12px; color:#94a3b8;">
                    <p style="margin:0 0 6px 0;">Trouble with the button? Copy and paste this URL into your browser:</p>
                    <a href="##SET_PASSWORD_URL##" style="color:#6366f1; word-break:break-all;">##SET_PASSWORD_URL##</a>
                    <p style="margin:12px 0 0 0;">&copy; ' . date('Y') . ' ##COMPANY_NAME##. All rights reserved.</p>
                </div>
            </div>';

            $htmlContent = strtr($templateBody, $replacements);
        }

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.staff_welcome_dynamic', ['htmlContent' => $htmlContent]);
    }
}
