<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\Ticket;
use App\Models\TicketEmailTemplate;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TicketNotificationService
{
    private HelpdeskEmailService $emailService;

    public function __construct(HelpdeskEmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Check if a specific notification type is enabled
     */
    private function isNotificationEnabled(string $type): bool
    {
        return SystemSetting::getValue('notification', $type, true);
    }

    /**
     * Determine creator type based on user
     * 
     * @param User $user
     * @return string 'client', 'staff', or 'staff_assign'
     */
    public function determineCreatorType(User $user): string
    {
        // Check if user is a client
        $client = Client::where('user_id', $user->id)->first();
        if ($client) {
            return 'client';
        }

        // Check if user has helpdesk_tickets.assign permission (including Administrator)
        if ($this->hasAssignPermission($user)) {
            return 'staff_assign';
        }

        // Otherwise, user is regular staff without assign permission
        return 'staff';
    }

    /**
     * Check if user has helpdesk_tickets.assign permission
     * 
     * @param User $user
     * @return bool
     */
    private function hasAssignPermission(User $user): bool
    {
        if (!$user->role) {
            return false;
        }

        // Administrator has all permissions
        if ($user->role->name === 'Administrator') {
            return true;
        }

        $permissions = $user->role->permissions ?? [];
        
        if (is_array($permissions)) {
            return in_array('helpdesk_tickets.assign', $permissions);
        }

        return false;
    }

    /**
     * Get staff users with helpdesk_tickets.assign permission ONLY
     * These are the staff who should receive notifications when client creates ticket
     * Excludes client users
     * Only includes users who have employee record with valid email
     * 
     * @return Collection<User>
     */
    public function getHelpdeskStaffRecipients(): Collection
    {
        // Get all user IDs that are clients (to exclude them)
        $clientUserIds = Client::whereNotNull('user_id')->pluck('user_id');

        return User::whereHas('role', function ($query) {
            $query->where('is_active', true)
                  ->where(function ($q) {
                      // ONLY check for helpdesk_tickets.assign permission
                      // This is the permission that determines who receives ticket notifications
                      $q->whereJsonContains('permissions', 'helpdesk_tickets.assign');
                  });
        })
        ->whereNotIn('id', $clientUserIds) // Exclude client users
        ->where('is_active', true)
        // Must have employee record with valid email
        ->whereHas('employee', function ($q) {
            $q->whereNotNull('email')
              ->where('email', '!=', '')
              ->where('status', 'active');
        })
        ->with('employee') // Eager load employee for email
        ->get();
    }

    /**
     * Get email address for a staff user (from Employee record ONLY)
     * NO FALLBACK to user email - staff MUST have employee record with email
     * 
     * @param User $user
     * @return string|null
     */
    public function getStaffEmail(User $user): ?string
    {
        // ONLY use employee's email - no fallback to user email
        if ($user->employee && !empty($user->employee->email)) {
            return $user->employee->email;
        }
        
        // No fallback - staff must have employee record with email
        Log::warning('TicketNotificationService: Staff has no employee email, skipping', [
            'user_id' => $user->id,
            'user_name' => $user->name,
        ]);
        
        return null;
    }

    /**
     * Get staff name for email
     * Priority: Employee full_name > User name
     * 
     * @param User $user
     * @return string
     */
    public function getStaffName(User $user): string
    {
        // Use employee full_name if available
        if ($user->employee && !empty($user->employee->full_name)) {
            return $user->employee->full_name;
        }
        
        return $user->name;
    }

    /**
     * Get email address for a client
     * Priority: Client's own email (from clients table)
     * 
     * @param Client $client
     * @return string|null
     */
    public function getClientEmail(Client $client): ?string
    {
        // Use client's direct email from clients table
        if (!empty($client->email)) {
            return $client->email;
        }
        
        // No fallback - client must have email in clients table
        Log::warning('TicketNotificationService: Client has no email in clients table', [
            'client_id' => $client->id,
            'client_name' => $client->name,
        ]);
        
        return null;
    }

    /**
     * Get client name for email
     * 
     * @param Client $client
     * @return string
     */
    public function getClientName(Client $client): string
    {
        // Use contact person if available, otherwise client name
        if (!empty($client->contact_person)) {
            return $client->contact_person;
        }
        
        return $client->name;
    }

    /**
     * Get all staff users (for admin-created tickets)
     * Excludes client users
     * Only includes users who have employee record with valid email
     * 
     * @return Collection<User>
     */
    public function getAllStaffRecipients(): Collection
    {
        // Get all user IDs that are clients
        $clientUserIds = Client::whereNotNull('user_id')->pluck('user_id');

        return User::whereNotIn('id', $clientUserIds)
            ->where('is_active', true)
            ->whereNotNull('role_id')
            // Must have employee record with valid email
            ->whereHas('employee', function ($q) {
                $q->whereNotNull('email')
                  ->where('email', '!=', '')
                  ->where('status', 'active');
            })
            ->with('employee') // Eager load employee for email
            ->get();
    }

    /**
     * Get email template by slug
     * 
     * @param string $slug
     * @return TicketEmailTemplate|null
     */
    public function getTemplate(string $slug): ?TicketEmailTemplate
    {
        return TicketEmailTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Parse template placeholders with ticket data
     * 
     * @param string $content
     * @param Ticket $ticket
     * @param User $recipient
     * @return string
     */
    public function parseTemplate(string $content, Ticket $ticket, User $recipient): string
    {
        $placeholders = [
            'ticket_number' => $ticket->ticket_number,
            'ticket_subject' => $ticket->subject,
            'ticket_status' => $ticket->ticketStatus?->name ?? ucfirst($ticket->status),
            'ticket_priority' => $ticket->ticketPriority?->name ?? ucfirst($ticket->priority),
            'ticket_url' => route('helpdesk.show', $ticket),
            'name' => $recipient->name,
            'first_name' => explode(' ', $recipient->name)[0],
            'site_title' => config('app.name'),
            'site_url' => config('app.url'),
        ];

        foreach ($placeholders as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value ?? '', $content);
        }

        return $content;
    }

    /**
     * Send notifications for new ticket
     * 
     * @param Ticket $ticket The created ticket
     * @param User $creator The user who created the ticket
     * @param string $creatorType 'client', 'staff', or 'staff_assign'
     * @return void
     */
    public function sendNewTicketNotifications(Ticket $ticket, User $creator, string $creatorType): void
    {
        // Check if ticket created notification is enabled
        if (!$this->isNotificationEnabled('email_ticket_created')) {
            Log::info('TicketNotificationService: Ticket created notification disabled in settings', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        if (!$this->emailService->isConfigured()) {
            Log::warning('TicketNotificationService: Email not configured, skipping notifications', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
            ]);
            return;
        }

        switch ($creatorType) {
            case 'client':
                $this->handleClientCreatedTicket($ticket, $creator);
                break;
            case 'staff':
                // Staff WITHOUT assign permission - only notify staff with assign permission
                $this->handleStaffCreatedTicket($ticket, $creator);
                break;
            case 'staff_assign':
                // Staff WITH assign permission - notify client AND all employees
                $this->handleStaffAssignCreatedTicket($ticket, $creator);
                break;
        }
    }

    /**
     * Handle notifications when client creates ticket
     * - Send confirmation to client (using client's email, not user email)
     * - Send notification to staff with helpdesk_tickets.assign permission ONLY
     */
    private function handleClientCreatedTicket(Ticket $ticket, User $creator): void
    {
        // 1. Send confirmation to client
        $client = Client::where('user_id', $creator->id)->first();
        
        if ($client) {
            $clientEmail = $this->getClientEmail($client);
            $clientName = $this->getClientName($client);
            
            if ($clientEmail) {
                $clientTemplate = $this->getTemplate('new_ticket_confirmation');
                if ($clientTemplate) {
                    $this->sendEmailToRecipient($ticket, $clientEmail, $clientName, $clientTemplate);
                } else {
                    Log::warning('TicketNotificationService: Template new_ticket_confirmation not found');
                }
            } else {
                Log::warning('TicketNotificationService: Client has no email', [
                    'client_id' => $client->id,
                    'ticket_id' => $ticket->id,
                ]);
            }
        }

        // 2. Send notification to staff with helpdesk_tickets.assign permission ONLY
        $staffTemplate = $this->getTemplate('new_ticket_admin');
        if (!$staffTemplate) {
            Log::warning('TicketNotificationService: Template new_ticket_admin not found');
            return;
        }

        $staffRecipients = $this->getHelpdeskStaffRecipients();

        if ($staffRecipients->isEmpty()) {
            Log::warning('TicketNotificationService: No staff with helpdesk_tickets.assign permission found', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        Log::info('TicketNotificationService: Sending client ticket notification to staff with assign permission', [
            'ticket_id' => $ticket->id,
            'recipient_count' => $staffRecipients->count(),
            'recipients' => $staffRecipients->map(fn($u) => $u->employee?->email)->filter()->toArray(),
        ]);

        foreach ($staffRecipients as $staff) {
            $this->sendEmailToUser($ticket, $staff, $staffTemplate);
        }
    }

    /**
     * Handle notifications when staff WITHOUT assign permission creates ticket
     * - Send notification to staff with helpdesk_tickets.assign permission ONLY
     * - Do NOT send to client
     */
    private function handleStaffCreatedTicket(Ticket $ticket, User $creator): void
    {
        $staffTemplate = $this->getTemplate('new_ticket_admin');
        if (!$staffTemplate) {
            Log::warning('TicketNotificationService: Template new_ticket_admin not found');
            return;
        }

        $staffRecipients = $this->getHelpdeskStaffRecipients();
        
        // Exclude the creator from recipients
        $staffRecipients = $staffRecipients->filter(function ($user) use ($creator) {
            return $user->id !== $creator->id;
        });

        if ($staffRecipients->isEmpty()) {
            Log::warning('TicketNotificationService: No staff with assign permission found for staff ticket', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        Log::info('TicketNotificationService: Staff (no assign) created ticket - notifying staff with assign permission only', [
            'ticket_id' => $ticket->id,
            'recipient_count' => $staffRecipients->count(),
            'recipients' => $staffRecipients->map(fn($u) => $u->employee?->email)->filter()->toArray(),
        ]);

        foreach ($staffRecipients as $staff) {
            $this->sendEmailToUser($ticket, $staff, $staffTemplate);
        }
    }

    /**
     * Handle notifications when staff WITH assign permission creates ticket
     * - Send notification to client (if ticket has client)
     * - Send notification to ALL employees
     */
    private function handleStaffAssignCreatedTicket(Ticket $ticket, User $creator): void
    {
        // 1. Send notification to client if ticket has client
        if ($ticket->client_id) {
            $client = Client::find($ticket->client_id);
            if ($client) {
                $clientEmail = $this->getClientEmail($client);
                $clientName = $this->getClientName($client);
                
                if ($clientEmail) {
                    $clientTemplate = $this->getTemplate('new_ticket_confirmation');
                    if ($clientTemplate) {
                        $this->sendEmailToRecipient($ticket, $clientEmail, $clientName, $clientTemplate);
                        Log::info('TicketNotificationService: Staff (with assign) created ticket - notified client', [
                            'ticket_id' => $ticket->id,
                            'client_email' => $clientEmail,
                        ]);
                    } else {
                        Log::warning('TicketNotificationService: Template new_ticket_confirmation not found');
                    }
                }
            }
        }

        // 2. Send notification to ALL employees (except creator)
        $staffTemplate = $this->getTemplate('new_ticket_admin');
        if (!$staffTemplate) {
            Log::warning('TicketNotificationService: Template new_ticket_admin not found');
            return;
        }

        $allStaffRecipients = $this->getAllStaffRecipients();
        
        // Exclude the creator from recipients
        $allStaffRecipients = $allStaffRecipients->filter(function ($user) use ($creator) {
            return $user->id !== $creator->id;
        });

        if ($allStaffRecipients->isEmpty()) {
            Log::warning('TicketNotificationService: No staff recipients found for staff_assign ticket', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        Log::info('TicketNotificationService: Staff (with assign) created ticket - notifying all employees', [
            'ticket_id' => $ticket->id,
            'recipient_count' => $allStaffRecipients->count(),
            'recipients' => $allStaffRecipients->map(fn($u) => $u->employee?->email)->filter()->toArray(),
        ]);

        foreach ($allStaffRecipients as $staff) {
            $this->sendEmailToUser($ticket, $staff, $staffTemplate);
        }
    }

    /**
     * Send email to a specific staff user using template
     * Uses employee email instead of user email
     */
    private function sendEmailToUser(Ticket $ticket, User $recipient, TicketEmailTemplate $template): void
    {
        $email = $this->getStaffEmail($recipient);
        $name = $this->getStaffName($recipient);
        
        if (!$email) {
            Log::warning('TicketNotificationService: Staff has no email, skipping', [
                'user_id' => $recipient->id,
                'ticket_id' => $ticket->id,
            ]);
            return;
        }
        
        $this->sendEmailToRecipient($ticket, $email, $name, $template);
    }

    /**
     * Send email to a specific recipient (email address) using template
     */
    private function sendEmailToRecipient(Ticket $ticket, string $email, string $name, TicketEmailTemplate $template): void
    {
        try {
            // Create a temporary object for template parsing
            $recipientData = (object) ['name' => $name, 'email' => $email];
            
            $subject = $this->parseTemplateWithData($template->subject, $ticket, $name);
            $content = $this->parseTemplateWithData($template->content, $ticket, $name);

            // Wrap content in HTML template
            $htmlContent = view('emails.ticket-notification', [
                'content' => nl2br(e($content)),
                'ticket' => $ticket,
                'recipientName' => $name,
            ])->render();

            $this->emailService->send(
                $email,
                $name,
                $subject,
                $htmlContent
            );

            Log::info('TicketNotificationService: Email sent', [
                'ticket_id' => $ticket->id,
                'recipient' => $email,
                'template' => $template->slug,
            ]);
        } catch (\Exception $e) {
            Log::error('TicketNotificationService: Failed to send email', [
                'ticket_id' => $ticket->id,
                'recipient' => $email,
                'template' => $template->slug,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Parse template placeholders with ticket data and recipient name
     */
    private function parseTemplateWithData(string $content, Ticket $ticket, string $recipientName): string
    {
        $placeholders = [
            'ticket_number' => $ticket->ticket_number,
            'ticket_subject' => $ticket->subject,
            'ticket_status' => $ticket->ticketStatus?->name ?? ucfirst($ticket->status),
            'ticket_priority' => $ticket->ticketPriority?->name ?? ucfirst($ticket->priority),
            'ticket_url' => route('helpdesk.show', $ticket),
            'name' => $recipientName,
            'first_name' => explode(' ', $recipientName)[0],
            'site_title' => config('app.name'),
            'site_url' => config('app.url'),
        ];

        foreach ($placeholders as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value ?? '', $content);
        }

        return $content;
    }

    /**
     * Send notification to assignees when ticket is assigned
     * Uses employee email for all assignees
     * 
     * @param Ticket $ticket The ticket being assigned
     * @param array $assigneeUserIds Array of user IDs being assigned
     * @return void
     */
    public function sendAssignmentNotifications(Ticket $ticket, array $assigneeUserIds): void
    {
        // Check if ticket assigned notification is enabled
        if (!$this->isNotificationEnabled('email_ticket_assigned')) {
            Log::info('TicketNotificationService: Ticket assigned notification disabled in settings', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        if (!$this->emailService->isConfigured()) {
            Log::warning('TicketNotificationService: Email not configured, skipping assignment notifications', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        if (empty($assigneeUserIds)) {
            return;
        }

        $template = $this->getTemplate('ticket_assigned');
        if (!$template) {
            // Fallback to new_ticket_admin if ticket_assigned template doesn't exist
            $template = $this->getTemplate('new_ticket_admin');
            if (!$template) {
                Log::warning('TicketNotificationService: No template found for assignment notification');
                return;
            }
        }

        // Get assignee users with their employee records
        $assignees = User::whereIn('id', $assigneeUserIds)
            ->where('is_active', true)
            ->with('employee')
            ->get();

        foreach ($assignees as $assignee) {
            $email = $this->getStaffEmail($assignee);
            $name = $this->getStaffName($assignee);
            
            if (!$email) {
                Log::warning('TicketNotificationService: Assignee has no email, skipping', [
                    'user_id' => $assignee->id,
                    'ticket_id' => $ticket->id,
                ]);
                continue;
            }

            $this->sendEmailToRecipient($ticket, $email, $name, $template);
        }

        Log::info('TicketNotificationService: Assignment notifications sent', [
            'ticket_id' => $ticket->id,
            'assignee_count' => count($assigneeUserIds),
        ]);
    }

    /**
     * Send notification when ticket status changes
     * - Notifies client (if exists)
     * - Notifies all assignees
     * - Notifies staff with helpdesk_tickets.assign permission
     * - Uses different templates for Resolved/Closed vs other statuses
     * 
     * @param Ticket $ticket The ticket with updated status
     * @param string $oldStatus Previous status
     * @return void
     */
    public function sendStatusChangeNotifications(Ticket $ticket, string $oldStatus): void
    {
        // Check if ticket status changed notification is enabled
        if (!$this->isNotificationEnabled('email_ticket_status_changed')) {
            Log::info('TicketNotificationService: Ticket status changed notification disabled in settings', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        if (!$this->emailService->isConfigured()) {
            Log::warning('TicketNotificationService: Email not configured, skipping status change notifications', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        // Determine which template to use based on new status
        $newStatus = strtolower($ticket->status);
        $templateSlug = 'ticket_status_updated'; // Default for Open, In Progress, Pending, On Hold
        
        if ($newStatus === 'resolved') {
            $templateSlug = 'ticket_resolved';
        } elseif ($newStatus === 'closed') {
            $templateSlug = 'ticket_closed';
        }

        $template = $this->getTemplate($templateSlug);
        if (!$template) {
            // Fallback to default status template
            $template = $this->getTemplate('ticket_status_updated');
            if (!$template) {
                Log::warning('TicketNotificationService: No status template found', [
                    'attempted_slug' => $templateSlug,
                ]);
                return;
            }
        }

        $notifiedEmails = []; // Track to avoid duplicate emails

        // 1. Notify client if ticket has client
        if ($ticket->client_id) {
            $client = Client::find($ticket->client_id);
            if ($client) {
                $clientEmail = $this->getClientEmail($client);
                $clientName = $this->getClientName($client);
                
                if ($clientEmail) {
                    $this->sendEmailToRecipient($ticket, $clientEmail, $clientName, $template);
                    $notifiedEmails[] = $clientEmail;
                }
            }
        }

        // 2. Notify all assignees
        $ticket->load('assignees.employee');
        foreach ($ticket->assignees as $assignee) {
            $email = $this->getStaffEmail($assignee);
            $name = $this->getStaffName($assignee);
            
            if ($email && !in_array($email, $notifiedEmails)) {
                $this->sendEmailToRecipient($ticket, $email, $name, $template);
                $notifiedEmails[] = $email;
            }
        }

        // 3. Notify staff with helpdesk_tickets.assign permission
        $staffWithAssign = $this->getHelpdeskStaffRecipients();
        foreach ($staffWithAssign as $staff) {
            $email = $this->getStaffEmail($staff);
            $name = $this->getStaffName($staff);
            
            if ($email && !in_array($email, $notifiedEmails)) {
                $this->sendEmailToRecipient($ticket, $email, $name, $template);
                $notifiedEmails[] = $email;
            }
        }

        Log::info('TicketNotificationService: Status change notifications sent', [
            'ticket_id' => $ticket->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'template' => $templateSlug,
            'recipient_count' => count($notifiedEmails),
        ]);
    }

    /**
     * Send notification when a reply is added to ticket
     * - Normal reply: Notifies assignees, owner (client), and staff with helpdesk_tickets.assign
     * - Internal note: Only notifies assignees and staff with helpdesk_tickets.assign
     * 
     * @param Ticket $ticket The ticket
     * @param User $replier The user who replied
     * @param bool $isInternalNote Whether this is an internal note (staff only)
     * @return void
     */
    public function sendReplyNotifications(Ticket $ticket, User $replier, bool $isInternalNote = false): void
    {
        // Check if ticket replied notification is enabled
        if (!$this->isNotificationEnabled('email_ticket_replied')) {
            Log::info('TicketNotificationService: Ticket replied notification disabled in settings', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        if (!$this->emailService->isConfigured()) {
            Log::warning('TicketNotificationService: Email not configured, skipping reply notifications', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        $template = $this->getTemplate('ticket_reply');
        if (!$template) {
            Log::warning('TicketNotificationService: Template ticket_reply not found');
            return;
        }

        $notifiedEmails = []; // Track to avoid duplicate emails
        $replierEmail = null;
        
        // Get replier's email to exclude from notifications
        if ($replier->employee && !empty($replier->employee->email)) {
            $replierEmail = $replier->employee->email;
        }

        // Internal notes: Only notify assignees and staff with helpdesk_tickets.assign
        if ($isInternalNote) {
            // 1. Notify all assignees (except replier)
            $ticket->load('assignees.employee');
            foreach ($ticket->assignees as $assignee) {
                $email = $this->getStaffEmail($assignee);
                $name = $this->getStaffName($assignee);
                
                if ($email && $email !== $replierEmail && !in_array($email, $notifiedEmails)) {
                    $this->sendEmailToRecipient($ticket, $email, $name, $template);
                    $notifiedEmails[] = $email;
                }
            }

            // 2. Notify staff with helpdesk_tickets.assign permission (except replier)
            $staffWithAssign = $this->getHelpdeskStaffRecipients();
            foreach ($staffWithAssign as $staff) {
                if ($staff->id === $replier->id) continue;
                
                $email = $this->getStaffEmail($staff);
                $name = $this->getStaffName($staff);
                
                if ($email && !in_array($email, $notifiedEmails)) {
                    $this->sendEmailToRecipient($ticket, $email, $name, $template);
                    $notifiedEmails[] = $email;
                }
            }

            Log::info('TicketNotificationService: Internal note notifications sent', [
                'ticket_id' => $ticket->id,
                'recipient_count' => count($notifiedEmails),
            ]);
            return;
        }

        // Normal reply: Notify assignees, client (owner), and staff with helpdesk_tickets.assign
        
        // 1. Notify client (owner) if ticket has client and replier is not client
        $replierType = $this->determineCreatorType($replier);
        if ($ticket->client_id && $replierType !== 'client') {
            $client = Client::find($ticket->client_id);
            if ($client) {
                $clientEmail = $this->getClientEmail($client);
                $clientName = $this->getClientName($client);
                
                if ($clientEmail && !in_array($clientEmail, $notifiedEmails)) {
                    $this->sendEmailToRecipient($ticket, $clientEmail, $clientName, $template);
                    $notifiedEmails[] = $clientEmail;
                }
            }
        }

        // 2. Notify all assignees (except replier)
        $ticket->load('assignees.employee');
        foreach ($ticket->assignees as $assignee) {
            if ($assignee->id === $replier->id) continue;
            
            $email = $this->getStaffEmail($assignee);
            $name = $this->getStaffName($assignee);
            
            if ($email && !in_array($email, $notifiedEmails)) {
                $this->sendEmailToRecipient($ticket, $email, $name, $template);
                $notifiedEmails[] = $email;
            }
        }

        // 3. Notify staff with helpdesk_tickets.assign permission (except replier)
        $staffWithAssign = $this->getHelpdeskStaffRecipients();
        foreach ($staffWithAssign as $staff) {
            if ($staff->id === $replier->id) continue;
            
            $email = $this->getStaffEmail($staff);
            $name = $this->getStaffName($staff);
            
            if ($email && !in_array($email, $notifiedEmails)) {
                $this->sendEmailToRecipient($ticket, $email, $name, $template);
                $notifiedEmails[] = $email;
            }
        }

        Log::info('TicketNotificationService: Reply notifications sent', [
            'ticket_id' => $ticket->id,
            'replier_type' => $replierType,
            'is_internal_note' => $isInternalNote,
            'recipient_count' => count($notifiedEmails),
        ]);
    }

    /**
     * Notify all assignees except a specific user
     */
    private function notifyAssigneesExcept(Ticket $ticket, ?int $excludeUserId, TicketEmailTemplate $template): void
    {
        $ticket->load('assignees.employee');
        
        foreach ($ticket->assignees as $assignee) {
            if ($excludeUserId && $assignee->id === $excludeUserId) {
                continue;
            }
            
            $email = $this->getStaffEmail($assignee);
            $name = $this->getStaffName($assignee);
            
            if ($email) {
                $this->sendEmailToRecipient($ticket, $email, $name, $template);
            }
        }
    }
}
