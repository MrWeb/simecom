<?php

namespace App\Jobs;

use App\Mail\VideoCampaignMail;
use App\Models\VideoCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 300;

    public function __construct(
        protected VideoCampaign $campaign
    ) {}

    public function handle(): void
    {
        if ($this->campaign->video_status !== 'ready') {
            Log::warning('Attempted to send email for non-ready campaign', [
                'campaign_id' => $this->campaign->id,
                'video_status' => $this->campaign->video_status,
            ]);
            return;
        }

        try {
            Mail::to($this->campaign->email)
                ->send(new VideoCampaignMail($this->campaign));

            $this->campaign->update([
                'email_status' => 'sent',
                'email_sent_at' => now(),
            ]);

            Log::info('Campaign email sent', [
                'campaign_id' => $this->campaign->id,
                'email' => $this->campaign->email,
            ]);

        } catch (\Exception $e) {
            $this->campaign->update(['email_status' => 'failed']);

            Log::error('Failed to send campaign email', [
                'campaign_id' => $this->campaign->id,
                'email' => $this->campaign->email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
