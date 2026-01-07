<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLINE - Test Email Connection</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 40px 30px; border-radius: 8px 8px 0 0; text-align: center;">
                            <div style="width: 60px; height: 60px; background-color: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 30px; color: #ffffff;">✓</span>
                            </div>
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">Connection Successful!</h1>
                            <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 14px;">Your email configuration is working correctly</p>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 20px;">
                                Hello,
                            </p>
                            <p style="color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 20px;">
                                This is a test email from <strong>ATLINE System</strong> to verify that your email integration is configured correctly.
                            </p>
                            
                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <span style="font-size: 20px;">📧</span>
                                                </td>
                                                <td>
                                                    <p style="color: #166534; font-size: 14px; font-weight: 600; margin: 0 0 5px;">Email Configuration Details</p>
                                                    <p style="color: #15803d; font-size: 13px; margin: 0; line-height: 1.5;">
                                                        Sent at: {{ now()->format('d/m/Y H:i:s') }}<br>
                                                        From: {{ $fromName }} &lt;{{ $fromAddress }}&gt;
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #6b7280; font-size: 14px; line-height: 1.6; margin: 0;">
                                If you received this email, your SMTP settings are configured correctly and ready to send emails from your application.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 40px; border-radius: 0 0 8px 8px; border-top: 1px solid #e5e7eb;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                            © {{ date('Y') }} ATLINE System. All rights reserved.
                                        </p>
                                    </td>
                                    <td align="right">
                                        <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                            Asset & Project Management
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                
                <!-- Bottom Text -->
                <p style="color: #9ca3af; font-size: 11px; margin-top: 20px; text-align: center;">
                    This is an automated test email. Please do not reply.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
