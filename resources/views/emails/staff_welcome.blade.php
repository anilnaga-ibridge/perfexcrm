<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $appName }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f8;
            font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f4f6f8;
            padding: 40px 15px;
        }
        .email-container {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .email-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #d946ef 100%);
            padding: 32px 24px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .email-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 13px;
            margin: 6px 0 0 0;
        }
        .email-body {
            padding: 36px 32px;
        }
        .welcome-greeting {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .body-text {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .details-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 28px;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 13px;
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .details-label {
            color: #64748b;
            font-weight: 600;
        }
        .details-value {
            color: #1e293b;
            font-weight: 700;
        }
        .btn-wrapper {
            text-align: center;
            margin: 32px 0;
        }
        .action-btn {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #d946ef 100%);
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 99px;
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.3);
            transition: all 0.3s ease;
        }
        .info-box {
            background: #f0fdf4;
            border-left: 4px solid #16a34a;
            padding: 14px 18px;
            border-radius: 6px;
            font-size: 13px;
            color: #166534;
            margin-bottom: 24px;
        }
        .security-notice {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            margin-top: 24px;
            border-top: 1px solid #f1f5f9;
            padding-top: 16px;
        }
        .email-footer {
            background: #f8fafc;
            padding: 24px 32px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #94a3b8;
        }
        .email-footer a {
            color: #6366f1;
            text-decoration: none;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1>{{ $appName }}</h1>
                <p>Staff Account Activation</p>
            </div>
            
            <div class="email-body">
                <div class="welcome-greeting">Welcome aboard, {{ $user->name }}! 🎉</div>
                <p class="body-text">
                    An account has been created for you on <strong>{{ $appName }}</strong>. Below are your account registration details:
                </p>

                <table style="width: 100%; border-collapse: collapse; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px;">
                    <tr>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; color: #64748b;">Full Name</td>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 700; color: #1e293b; text-align: right;">{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; color: #64748b;">Email Address</td>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 700; color: #1e293b; text-align: right;">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b;">Assigned Role</td>
                        <td style="padding: 12px 16px; font-size: 13px; font-weight: 700; color: #4f46e5; text-align: right;">{{ $roleName }}</td>
                    </tr>
                </table>

                <div class="info-box">
                    🔒 For security reasons, please click the button below to set up your account password and activate your access.
                </div>

                <div class="btn-wrapper">
                    <a href="{{ $resetUrl }}" class="action-btn">Set Your Password</a>
                </div>

                <div class="security-notice">
                    <p style="margin: 0 0 8px 0;"><strong>Security Reminder:</strong></p>
                    <ul style="margin: 0; padding-left: 18px;">
                        <li>This password setup link will expire in 60 minutes.</li>
                        <li>Never share your account credentials or setup link with anyone.</li>
                        <li>If you did not expect this invitation, please contact your system administrator.</li>
                    </ul>
                </div>
            </div>
            
            <div class="email-footer">
                <p style="margin: 0 0 8px 0;">If you are having trouble clicking the button, copy and paste the URL below into your browser:</p>
                <p style="margin: 0 0 16px 0;"><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
                <p style="margin: 0;">&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
