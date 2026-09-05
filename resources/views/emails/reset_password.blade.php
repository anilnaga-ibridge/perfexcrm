<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request - iBRIDGE DIGITAL CRM</title>
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
            margin: 4px 0 0 0;
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
        .btn-wrapper {
            text-align: center;
            margin: 32px 0;
        }
        .reset-btn {
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
            background: #f8fafc;
            border-left: 4px solid #7c3aed;
            padding: 14px 18px;
            border-radius: 6px;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 24px;
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
        .footer-copy {
            margin-top: 16px;
            font-size: 11px;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <h1>iBRIDGE DIGITAL CRM</h1>
                <p>Security & Account Access Services</p>
            </div>

            <!-- Body -->
            <div class="email-body">
                <div class="welcome-greeting">Hello,</div>
                <div class="body-text">
                    We received a request to reset the password for your <strong>iBRIDGE DIGITAL CRM</strong> account (<code>{{ $email }}</code>).
                </div>

                <div class="btn-wrapper">
                    <a href="{{ $resetUrl }}" class="reset-btn" target="_blank">
                        Reset Password &rarr;
                    </a>
                </div>

                <div class="info-box">
                    <strong>Notice:</strong> This password reset link will expire in <strong>60 minutes</strong>. If you did not request a password reset, no further action is required and your account remains secure.
                </div>

                <div class="body-text" style="margin-bottom: 0;">
                    Regards,<br>
                    <strong>iBRIDGE Digital Team</strong>
                </div>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p style="margin: 0 0 8px 0;">If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>

                <div class="footer-copy">
                    &copy; {{ date('Y') }} iBRIDGE DIGITAL. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
