<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use App\Services\HelpdeskEmailService;
use App\Services\TicketNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTicketNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 60, 300]; // Exponential backoff: 10s, 1min, 5min

    protected int $ticketId;
    protected int $creatorId;
    protected string $creatorType;

    public function __construct(int $ticketId, int $creatorId, string $creatorType)
    {
        $this->ticketId = $ticketId;
        $this->creatorId = $creatorId;
        $this->creatorType = $creatorType;
    }

    public function handle(HelpdeskEmailService $emailService): void
    {
        $ticket = Ticket::with(['client', 'ticketStatus', 'ticketPriority'])->find($this->ticketId);
        
        if (!$ticket) {
            Log::warning('SendTicketNotificationJob: Ticket not found', [
                'ticket_id' => $this->ticketId,
            ]);
            return;
        }

        $creator = User::find($this->creatorId);
        
        if (!$creator) {
            Log::warning('SendTicketNotificationJob: Creator not found', [
                'ticket_id' => $this->ticketId,
                'creator_id' => $this->creatorId,
            ]);
            return;
        }

        $notificationService = new TicketNotificationService($emailService);
        
        $notificationService->sendNewTicketNotifications($ticket, $creator, $this->creatorType);

        Log::info('SendTicketNotificationJob: Notifications sent', [
            'ticket_id' => $this->ticketId,
            'ticket_number' => $ticket->ticket_number,
            'creator_type' => $this->creatorType,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendTicketNotificationJob: Failed to send notifications', [
            'ticket_id' => $this->ticketId,
            'creator_id' => $this->creatorId,
            'creator_type' => $this->creatorType,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
