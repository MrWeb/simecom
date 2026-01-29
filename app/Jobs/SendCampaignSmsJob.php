<?php

namespace App\Jobs;

use App\Models\VideoCampaign;
use App\Services\BrevoSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 300;

    public function __construct(
        protected VideoCampaign $campaign
    ) {}

    public function handle(BrevoSmsService $smsService): void
    {
        if ($this->campaign->video_status !== 'ready') {
            Log::warning('Attempted to send SMS for non-ready campaign', [
                'campaign_id' => $this->campaign->id,
                'video_status' => $this->campaign->video_status,
            ]);
            return;
        }

        if (empty($this->campaign->phone)) {
            Log::warning('Attempted to send SMS without phone number', [
                'campaign_id' => $this->campaign->id,
            ]);
            return;
        }

        try {
            $landingUrl = $this->campaign->getLandingUrl();
            $customerName = $this->campaign->customer_name;

            // Messaggio SMS breve e conciso
            $message = "Ciao {$customerName}, abbiamo preparato un video personalizzato esclusivamente per la tua offerta Simecom!\nGuardalo qui: {$landingUrl}";

            $result = $smsService->send($this->campaign->phone, $message);

            if ($result['success']) {
                $this->campaign->update([
                    'sms_status' => 'sent',
                    'sms_sent_at' => now(),
                ]);

                Log::info('Campaign SMS sent', [
                    'campaign_id' => $this->campaign->id,
                    'phone' => $this->campaign->phone,
                    'message_id' => $result['message_id'],
                ]);
            } else {
                throw new \Exception($result['error'] ?? 'Errore sconosciuto invio SMS');
            }

        } catch (\Exception $e) {
            $this->campaign->update(['sms_status' => 'failed']);

            Log::error('Failed to send campaign SMS', [
                'campaign_id' => $this->campaign->id,
                'phone' => $this->campaign->phone,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
