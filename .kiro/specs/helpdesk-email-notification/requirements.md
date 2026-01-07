# Requirements Document

## Introduction

Sistem ini akan mengintegrasikan email templates helpdesk dengan email provider settings yang dikonfigurasi di `/settings/integrations?tab=email`. Apabila ticket baru dicipta, sistem akan menghantar notifikasi email kepada penerima yang sesuai berdasarkan jenis ticket creation (Client, Staff, atau Admin) dan role permissions.

**Email Source:**
- Staff/Employee email diambil dari jadual `employees.email` (bukan `users.email`)
- Client email diambil dari jadual `clients.email` (bukan `users.email`)
- Semua notification (send, receive, notify) menggunakan email dari Employee/Client table

## Glossary

- **Email_Service**: Servis yang menguruskan penghantaran email menggunakan provider yang dikonfigurasi (SMTP/Google)
- **Ticket_Notification_Service**: Servis yang menentukan penerima email dan menghantar notifikasi berdasarkan jenis ticket
- **Helpdesk_Permission**: Permission `helpdesk_tickets.view` dalam role matrix yang menentukan siapa boleh menerima ticket notifications
- **Client_User**: User yang mempunyai rekod dalam jadual `clients` dengan `user_id` yang sama
- **Staff_User**: User yang mempunyai role dengan permission `helpdesk_tickets.view` dan mempunyai rekod Employee dengan email
- **Admin_User**: User dengan role Administrator
- **Email_Template**: Template email yang disimpan dalam jadual `ticket_email_templates` dengan placeholders
- **Employee_Email**: Email address dari jadual `employees` yang dikaitkan dengan User melalui `user_id`
- **Client_Email**: Email address dari jadual `clients`

## Requirements

### Requirement 1: Email Service Integration

**User Story:** As a system administrator, I want the helpdesk to use the configured email provider, so that all ticket notifications are sent through the centralized email settings.

#### Acceptance Criteria

1. WHEN sending ticket notification emails, THE Email_Service SHALL use the email provider configured in Integration Settings (SMTP or Google)
2. IF email provider is not configured or inactive, THEN THE Email_Service SHALL log the error and skip email sending without crashing
3. WHEN email credentials are invalid, THE Email_Service SHALL log the failure and continue system operation
4. THE Email_Service SHALL use `from_address` and `from_name` from Integration Settings for all outgoing emails

### Requirement 2: Email Source for Staff

**User Story:** As a system administrator, I want staff email notifications to use employee email addresses, so that notifications go to their work email.

#### Acceptance Criteria

1. WHEN sending email to Staff_User, THE Ticket_Notification_Service SHALL use email from `employees.email` field
2. WHEN Staff_User has no Employee record, THE Ticket_Notification_Service SHALL skip sending and log warning
3. WHEN Employee record has empty email, THE Ticket_Notification_Service SHALL skip sending and log warning
4. THE Ticket_Notification_Service SHALL use `employees.full_name` for recipient name in emails

### Requirement 3: Email Source for Client

**User Story:** As a system administrator, I want client email notifications to use client email addresses, so that notifications go to the correct contact.

#### Acceptance Criteria

1. WHEN sending email to Client, THE Ticket_Notification_Service SHALL use email from `clients.email` field
2. WHEN Client has no email in clients table, THE Ticket_Notification_Service SHALL skip sending and log warning
3. THE Ticket_Notification_Service SHALL use `clients.contact_person` or `clients.name` for recipient name

### Requirement 4: Client Opens New Ticket Notification

**User Story:** As a client, I want to receive confirmation when I open a new ticket, so that I know my request has been submitted successfully.

#### Acceptance Criteria

1. WHEN a Client_User creates a new ticket, THE Ticket_Notification_Service SHALL send confirmation email to the Client using `clients.email`
2. WHEN a Client_User creates a new ticket, THE Ticket_Notification_Service SHALL send notification email to all Staff_Users who have `helpdesk_tickets.view` permission using `employees.email`
3. WHEN sending to Staff_Users, THE Ticket_Notification_Service SHALL use `new_ticket_admin` template
4. THE Ticket_Notification_Service SHALL NOT send notification to Administrator users when Client creates ticket
5. WHEN no Staff_Users have `helpdesk_tickets.view` permission, THE Ticket_Notification_Service SHALL log warning and continue

### Requirement 5: Staff Creates New Ticket Notification

**User Story:** As a staff member, I want to create tickets for clients without notifying the client, so that I can handle internal matters discretely.

#### Acceptance Criteria

1. WHEN a Staff_User creates a new ticket, THE Ticket_Notification_Service SHALL send notification email to all Staff_Users who have `helpdesk_tickets.view` permission using `employees.email`
2. WHEN a Staff_User creates a new ticket, THE Ticket_Notification_Service SHALL NOT send any email to the Client associated with the ticket
3. WHEN a Staff_User creates a new ticket, THE Ticket_Notification_Service SHALL NOT display the ticket to the Client in their ticket list
4. THE Ticket_Notification_Service SHALL use `new_ticket_admin` template for Staff notification
5. WHEN no other Staff_Users have `helpdesk_tickets.view` permission, THE Ticket_Notification_Service SHALL log warning and continue

### Requirement 6: Admin Creates New Ticket Notification

**User Story:** As an administrator, I want to create tickets that notify all staff members, so that the team is aware of new issues.

#### Acceptance Criteria

1. WHEN an Admin_User creates a new ticket, THE Ticket_Notification_Service SHALL send notification email to all Staff_Users using `employees.email`
2. WHEN an Admin_User creates a new ticket, THE Ticket_Notification_Service SHALL NOT send any email to the Client
3. THE Ticket_Notification_Service SHALL use `new_ticket_admin` template for all Staff notifications
4. THE Ticket_Notification_Service SHALL include the Admin_User who created the ticket in the notification list if they have Employee email

### Requirement 7: Ticket Assignment Notification

**User Story:** As a staff member, I want to receive notification when a ticket is assigned to me, so that I can start working on it.

#### Acceptance Criteria

1. WHEN a ticket is assigned to Staff_User, THE Ticket_Notification_Service SHALL send notification email using `employees.email`
2. THE Ticket_Notification_Service SHALL use `ticket_assigned` template for assignment notification
3. WHEN multiple Staff_Users are assigned, THE Ticket_Notification_Service SHALL send notification to each assignee
4. THE Ticket_Notification_Service SHALL only notify newly assigned users (not existing assignees)

### Requirement 8: Ticket Reply Notification

**User Story:** As a ticket participant, I want to receive notification when someone replies to my ticket, so that I can respond promptly.

#### Acceptance Criteria

1. WHEN Client replies to ticket, THE Ticket_Notification_Service SHALL send notification to all assignees using `employees.email`
2. WHEN Staff replies to ticket, THE Ticket_Notification_Service SHALL send notification to Client using `clients.email`
3. WHEN Staff replies to ticket, THE Ticket_Notification_Service SHALL send notification to other assignees using `employees.email`
4. WHEN reply is internal note, THE Ticket_Notification_Service SHALL only notify other assignees (not Client)
5. THE Ticket_Notification_Service SHALL use `ticket_reply` template for reply notification

### Requirement 9: Ticket Status Change Notification

**User Story:** As a ticket participant, I want to receive notification when ticket status changes, so that I stay informed.

#### Acceptance Criteria

1. WHEN ticket status changes, THE Ticket_Notification_Service SHALL send notification to Client using `clients.email`
2. WHEN ticket status changes, THE Ticket_Notification_Service SHALL send notification to all assignees using `employees.email`
3. THE Ticket_Notification_Service SHALL use `ticket_status_updated` template for status change notification

### Requirement 10: Email Template Parsing

**User Story:** As a system, I want to parse email templates with actual ticket data, so that recipients receive personalized and informative emails.

#### Acceptance Criteria

1. WHEN parsing email template, THE Email_Service SHALL replace `{{ticket_number}}` with actual ticket number
2. WHEN parsing email template, THE Email_Service SHALL replace `{{ticket_subject}}` with actual ticket subject
3. WHEN parsing email template, THE Email_Service SHALL replace `{{ticket_status}}` with actual ticket status name
4. WHEN parsing email template, THE Email_Service SHALL replace `{{ticket_priority}}` with actual ticket priority name
5. WHEN parsing email template, THE Email_Service SHALL replace `{{ticket_url}}` with full URL to ticket detail page
6. WHEN parsing email template, THE Email_Service SHALL replace `{{name}}` with recipient's full name
7. WHEN parsing email template, THE Email_Service SHALL replace `{{first_name}}` with recipient's first name
8. WHEN parsing email template, THE Email_Service SHALL replace `{{site_title}}` with application name from config
9. WHEN parsing email template, THE Email_Service SHALL replace `{{site_url}}` with application URL from config

### Requirement 11: Recipient Determination by Permission

**User Story:** As a system administrator, I want email notifications to be sent based on role permissions, so that only authorized staff receive ticket notifications.

#### Acceptance Criteria

1. WHEN determining Staff recipients, THE Ticket_Notification_Service SHALL query users whose role has `helpdesk_tickets.view` permission
2. WHEN determining Staff recipients, THE Ticket_Notification_Service SHALL exclude users with inactive status
3. WHEN determining Staff recipients, THE Ticket_Notification_Service SHALL exclude users without Employee record
4. WHEN determining Staff recipients, THE Ticket_Notification_Service SHALL exclude users whose Employee has no email
5. WHEN role permissions change, THE Ticket_Notification_Service SHALL reflect changes in next notification

### Requirement 12: Email Queue Processing

**User Story:** As a system, I want to queue email notifications, so that ticket creation is not delayed by email sending.

#### Acceptance Criteria

1. WHEN sending ticket notifications, THE Email_Service SHALL dispatch emails to queue for async processing
2. IF queue processing fails, THEN THE Email_Service SHALL retry up to 3 times with exponential backoff
3. WHEN all retries fail, THE Email_Service SHALL log the failure with ticket and recipient details
4. THE Email_Service SHALL process queued emails within 5 minutes of ticket creation
