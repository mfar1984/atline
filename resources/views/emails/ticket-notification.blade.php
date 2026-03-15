<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Ticket Notification</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); padding: 40px 40px 30px; border-radius: 8px 8px 0 0; text-align: center;">
                            <!-- Logo -->
                            <div style="margin: 0 auto 20px;">
                                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="max-height: 60px; max-width: 200px;">
                            </div>
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">Ticket Notification</h1>
                            @if(isset($ticket))
                            <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 14px;">{{ $ticket->ticket_number }}</p>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 20px;">
                                Hello {{ $recipientName ?? 'User' }},
                            </p>
                            
                            <div style="color: #374151; font-size: 15px; line-height: 1.8; margin: 0 0 25px;">
                                {!! $content !!}
                            </div>
                            
                            @if(isset($ticket))
                            <!-- Ticket Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40" valign="top">
                                                    <span style="font-size: 20px;">📋</span>
                                                </td>
                                                <td>
                                                    <p style="color: #1e40af; font-size: 14px; font-weight: 600; margin: 0 0 10px;">Ticket Details</p>
                                                    <table width="100%" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="color: #6b7280; font-size: 13px; padding: 3px 0;">Ticket Number:</td>
                                                            <td style="color: #1f2937; font-size: 13px; padding: 3px 0; font-weight: 500;">{{ $ticket->ticket_number }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="color: #6b7280; font-size: 13px; padding: 3px 0;">Subject:</td>
                                                            <td style="color: #1f2937; font-size: 13px; padding: 3px 0; font-weight: 500;">{{ $ticket->subject }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="color: #6b7280; font-size: 13px; padding: 3px 0;">Status:</td>
                                                            <td style="color: #1f2937; font-size: 13px; padding: 3px 0; font-weight: 500;">{{ $ticket->ticketStatus?->name ?? ucfirst($ticket->status) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="color: #6b7280; font-size: 13px; padding: 3px 0;">Priority:</td>
                                                            <td style="color: #1f2937; font-size: 13px; padding: 3px 0; font-weight: 500;">{{ $ticket->ticketPriority?->name ?? ucfirst($ticket->priority) }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- View Ticket Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('helpdesk.show', $ticket) }}" style="display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 14px; font-weight: 600;">
                                            View Ticket
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            @endif
                            
                            <p style="color: #6b7280; font-size: 14px; line-height: 1.6; margin: 0;">
                                If you have any questions, please don't hesitate to contact us.
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
                                            © {{ date('Y') }} Atline Sdn Bhd. All rights reserved.
                                        </p>
                                    </td>
                                    <td align="right">
                                        <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                            Atline System
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                
                <!-- Bottom Text -->
                <p style="color: #9ca3af; font-size: 11px; margin-top: 20px; text-align: center;">
                    This is an automated notification. Please do not reply directly to this email.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
