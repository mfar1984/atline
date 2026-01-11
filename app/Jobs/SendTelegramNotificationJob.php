<?php

namespace App\Jobs;

use App\Services\TelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 5;

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
     * Execute the job.
     */
    public function handle(): void
    {
        TelegramNotificationService::sendActivityNotification($this->activityData);
    }
}
