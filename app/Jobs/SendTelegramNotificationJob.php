<?php

namespace App\Jobs;

use App\Services\TelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramNotificationJob
{
    use Dispatchable;

    /**
     * Activity data to send
     */
    protected array $activityData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $activityData)
    {
        $this->activityData = $activityData;
    }

    /**
     * Execute the job synchronously.
     */
    public function handle(): void
    {
        TelegramNotificationService::sendActivityNotification($this->activityData);
    }
}
