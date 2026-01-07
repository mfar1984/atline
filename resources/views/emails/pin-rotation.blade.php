<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your New Credential Vault PIN</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Credential Vault</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 14px;">Daily PIN Rotation</p>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px;">
        <p style="margin: 0 0 20px 0;">Hello {{ $user->name }},</p>
        
        <p style="margin: 0 0 20px 0;">Your Credential Vault PIN has been automatically rotated for security purposes. Here is your new PIN:</p>
        
        <div style="background: #059669; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0;">
            <p style="color: white; font-size: 32px; font-weight: bold; letter-spacing: 8px; margin: 0; font-family: 'Courier New', monospace;">{{ $pin }}</p>
        </div>
        
        <div style="background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0; color: #92400e; font-size: 14px;">
                <strong>⚠️ Important:</strong> Please save this PIN securely. You will need it to unlock your credentials. This PIN will expire in 24 hours.
            </p>
        </div>
        
        <p style="margin: 20px 0 0 0; font-size: 14px; color: #6b7280;">
            If you did not request this or have any concerns, please contact your system administrator immediately.
        </p>
    </div>
    
    <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 12px;">
        <p style="margin: 0;">This is an automated message from your Credential Vault system.</p>
        <p style="margin: 5px 0 0 0;">Please do not reply to this email.</p>
    </div>
</body>
</html>
