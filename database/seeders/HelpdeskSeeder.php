<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use App\Models\TicketEmailTemplate;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class HelpdeskSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Categories
        $categories = [
            ['name' => 'Technical Issue', 'description' => 'Hardware or software technical problems', 'color' => '#ef4444', 'icon' => 'build', 'sort_order' => 1, 'is_default' => false],
            ['name' => 'Billing', 'description' => 'Payment and invoice related inquiries', 'color' => '#f59e0b', 'icon' => 'receipt_long', 'sort_order' => 2, 'is_default' => false],
            ['name' => 'General Inquiry', 'description' => 'General questions and information requests', 'color' => '#3b82f6', 'icon' => 'help', 'sort_order' => 3, 'is_default' => true],
            ['name' => 'Feature Request', 'description' => 'Suggestions for new features or improvements', 'color' => '#8b5cf6', 'icon' => 'lightbulb', 'sort_order' => 4, 'is_default' => false],
            ['name' => 'Bug Report', 'description' => 'Report software bugs and issues', 'color' => '#dc2626', 'icon' => 'bug_report', 'sort_order' => 5, 'is_default' => false],
            ['name' => 'Network Issue', 'description' => 'Network connectivity and configuration problems', 'color' => '#06b6d4', 'icon' => 'wifi', 'sort_order' => 6, 'is_default' => false],
            ['name' => 'Security', 'description' => 'Security related concerns and incidents', 'color' => '#1f2937', 'icon' => 'security', 'sort_order' => 7, 'is_default' => false],
            ['name' => 'Training', 'description' => 'Training and documentation requests', 'color' => '#22c55e', 'icon' => 'school', 'sort_order' => 8, 'is_default' => false],
        ];

        foreach ($categories as $category) {
            TicketCategory::firstOrCreate(['name' => $category['name']], $category);
        }

        // Seed Priorities
        $priorities = [
            ['name' => 'Low', 'description' => 'Non-urgent issues that can wait', 'color' => '#22c55e', 'icon' => 'arrow_downward', 'level' => 1, 'sort_order' => 1, 'is_default' => true],
            ['name' => 'Medium', 'description' => 'Standard priority for regular issues', 'color' => '#f59e0b', 'icon' => 'remove', 'level' => 3, 'sort_order' => 2, 'is_default' => false],
            ['name' => 'High', 'description' => 'Important issues requiring prompt attention', 'color' => '#f97316', 'icon' => 'arrow_upward', 'level' => 5, 'sort_order' => 3, 'is_default' => false],
            ['name' => 'Urgent', 'description' => 'Critical issues requiring immediate action', 'color' => '#ef4444', 'icon' => 'priority_high', 'level' => 7, 'sort_order' => 4, 'is_default' => false],
            ['name' => 'Critical', 'description' => 'System down or major business impact', 'color' => '#dc2626', 'icon' => 'crisis_alert', 'level' => 10, 'sort_order' => 5, 'is_default' => false],
        ];

        foreach ($priorities as $priority) {
            TicketPriority::firstOrCreate(['name' => $priority['name']], $priority);
        }

        // Seed Statuses
        $statuses = [
            ['name' => 'Open', 'description' => 'New ticket awaiting assignment', 'color' => '#3b82f6', 'icon' => 'pending', 'sort_order' => 1, 'is_default' => true, 'is_closed' => false],
            ['name' => 'In Progress', 'description' => 'Ticket is being worked on', 'color' => '#f59e0b', 'icon' => 'autorenew', 'sort_order' => 2, 'is_default' => false, 'is_closed' => false],
            ['name' => 'Pending', 'description' => 'Waiting for customer response or information', 'color' => '#8b5cf6', 'icon' => 'hourglass_empty', 'sort_order' => 3, 'is_default' => false, 'is_closed' => false],
            ['name' => 'On Hold', 'description' => 'Ticket temporarily paused', 'color' => '#64748b', 'icon' => 'pause', 'sort_order' => 4, 'is_default' => false, 'is_closed' => false],
            ['name' => 'Resolved', 'description' => 'Issue has been resolved', 'color' => '#22c55e', 'icon' => 'check_circle', 'sort_order' => 5, 'is_default' => false, 'is_closed' => true],
            ['name' => 'Closed', 'description' => 'Ticket is closed and archived', 'color' => '#1f2937', 'icon' => 'task_alt', 'sort_order' => 6, 'is_default' => false, 'is_closed' => true],
        ];

        foreach ($statuses as $status) {
            TicketStatus::firstOrCreate(['name' => $status['name']], $status);
        }

        // Seed Email Templates
        $this->seedEmailTemplates();
    }

    private function seedEmailTemplates(): void
    {
        $templates = [
            // Client Templates
            [
                'slug' => 'new_ticket_confirmation',
                'title' => 'new_ticket_confirmation',
                'description' => '(Client) New ticket submitted confirmation',
                'recipient_type' => 'client',
                'subject' => 'Ticket #{{ticket_number}} Created - {{site_title}}',
                'content' => "Dear {{name}},

Thank you for contacting us. Your support ticket has been created successfully.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
Priority: {{ticket_priority}}
Status: {{ticket_status}}

You can track your ticket status here:
{{ticket_url}}

We will respond to your inquiry as soon as possible.

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            [
                'slug' => 'new_staff_reply',
                'title' => 'new_staff_reply',
                'description' => '(Client) New staff reply notification',
                'recipient_type' => 'client',
                'subject' => 'New Reply on Ticket #{{ticket_number}} - {{site_title}}',
                'content' => "Dear {{name}},

A staff member has replied to your support ticket.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}

Reply:
{{reply_content}}

You can view the full conversation and respond here:
{{ticket_url}}

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            [
                'slug' => 'ticket_status_changed',
                'title' => 'ticket_status_changed',
                'description' => '(Client) Ticket status changed notification',
                'recipient_type' => 'client',
                'subject' => 'Ticket #{{ticket_number}} Status Updated - {{site_title}}',
                'content' => "Dear {{name}},

The status of your support ticket has been updated.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
New Status: {{ticket_status}}

You can view your ticket here:
{{ticket_url}}

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            [
                'slug' => 'ticket_resolved',
                'title' => 'ticket_resolved',
                'description' => '(Client) Ticket resolved notification',
                'recipient_type' => 'client',
                'subject' => 'Ticket #{{ticket_number}} Has Been Resolved - {{site_title}}',
                'content' => "Dear {{name}},

Great news! Your support ticket has been resolved.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
Resolution Date: {{resolved_date}}

If you are satisfied with the resolution, no further action is needed. The ticket will be automatically closed after a few days.

If you have any further questions or the issue persists, please reply to this ticket:
{{ticket_url}}

Thank you for your patience.

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            [
                'slug' => 'ticket_closed',
                'title' => 'ticket_closed',
                'description' => '(Client) Ticket closed notification',
                'recipient_type' => 'client',
                'subject' => 'Ticket #{{ticket_number}} Has Been Closed - {{site_title}}',
                'content' => "Dear {{name}},

Your support ticket has been closed.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
Closed Date: {{closed_date}}

If you need further assistance regarding this issue, you can create a new ticket referencing this ticket number.

We value your feedback! Please let us know how we did.

Thank you for choosing {{site_title}}.

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            // Staff Templates
            [
                'slug' => 'ticket_assigned_to_you',
                'title' => 'ticket_assigned_to_you',
                'description' => '(Staff) A ticket was assigned to you',
                'recipient_type' => 'staff',
                'subject' => 'Ticket #{{ticket_number}} Assigned to You - {{site_title}}',
                'content' => "Dear {{name}},

A support ticket has been assigned to you.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
Priority: {{ticket_priority}}
Status: {{ticket_status}}

Please review and respond to this ticket:
{{ticket_url}}

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            [
                'slug' => 'new_customer_reply',
                'title' => 'new_customer_reply',
                'description' => '(Staff) New customer reply notification',
                'recipient_type' => 'staff',
                'subject' => 'Customer Reply on Ticket #{{ticket_number}} - {{site_title}}',
                'content' => "Dear {{name}},

A customer has replied to a support ticket assigned to you.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
Priority: {{ticket_priority}}

Reply:
{{reply_content}}

Please review and respond:
{{ticket_url}}

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            [
                'slug' => 'new_internal_note',
                'title' => 'new_internal_note',
                'description' => '(Staff) New internal note added',
                'recipient_type' => 'staff',
                'subject' => 'Internal Note on Ticket #{{ticket_number}} - {{site_title}}',
                'content' => "Dear {{name}},

A new internal note has been added to a support ticket.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
Priority: {{ticket_priority}}

Internal Note:
{{reply_content}}

View the ticket:
{{ticket_url}}

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            [
                'slug' => 'ticket_status_changed_staff',
                'title' => 'ticket_status_changed_staff',
                'description' => '(Staff) Ticket status changed notification',
                'recipient_type' => 'staff',
                'subject' => 'Ticket #{{ticket_number}} Status Updated - {{site_title}}',
                'content' => "Dear {{name}},

The status of a support ticket assigned to you has been updated.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
New Status: {{ticket_status}}
Priority: {{ticket_priority}}

View the ticket:
{{ticket_url}}

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            // Admin Templates
            [
                'slug' => 'new_ticket_admin',
                'title' => 'new_ticket_admin',
                'description' => '(Admin) New ticket submitted notification',
                'recipient_type' => 'admin',
                'subject' => 'New Ticket #{{ticket_number}} Submitted - {{site_title}}',
                'content' => "Dear {{name}},

A new support ticket has been submitted and requires assignment.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
Priority: {{ticket_priority}}
Status: {{ticket_status}}

Please review and assign this ticket:
{{ticket_url}}

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            // Additional templates for notifications
            [
                'slug' => 'ticket_assigned',
                'title' => 'ticket_assigned',
                'description' => '(Staff) Ticket assigned notification',
                'recipient_type' => 'staff',
                'subject' => 'Ticket #{{ticket_number}} Assigned to You - {{site_title}}',
                'content' => "Dear {{name}},

A support ticket has been assigned to you.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
Priority: {{ticket_priority}}
Status: {{ticket_status}}

Please review and respond to this ticket:
{{ticket_url}}

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            [
                'slug' => 'ticket_status_updated',
                'title' => 'ticket_status_updated',
                'description' => '(All) Ticket status updated notification',
                'recipient_type' => 'client',
                'subject' => 'Ticket #{{ticket_number}} Status Updated - {{site_title}}',
                'content' => "Dear {{name}},

The status of your support ticket has been updated.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
New Status: {{ticket_status}}
Priority: {{ticket_priority}}

You can view your ticket here:
{{ticket_url}}

Sincerely,
{{site_title}}
{{site_url}}",
            ],
            [
                'slug' => 'ticket_reply',
                'title' => 'ticket_reply',
                'description' => '(All) New reply notification',
                'recipient_type' => 'client',
                'subject' => 'New Reply on Ticket #{{ticket_number}} - {{site_title}}',
                'content' => "Dear {{name}},

There is a new reply on your support ticket.

Ticket Number: {{ticket_number}}
Subject: {{ticket_subject}}
Status: {{ticket_status}}

Please view the conversation here:
{{ticket_url}}

Sincerely,
{{site_title}}
{{site_url}}",
            ],
        ];

        foreach ($templates as $template) {
            TicketEmailTemplate::firstOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
