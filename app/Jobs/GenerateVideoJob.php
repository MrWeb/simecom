<?php

namespace App\Jobs;

use App\Models\VideoCampaign;
use App\Services\VideoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 1800; // 30 minuti

    public int $backoff = 120;

    public function __construct(
        protected VideoCampaign $campaign
    ) {}

    public function handle(VideoService $videoService): void
    {
        $this->campaign->update(['video_status' => 'processing']);

        try {
            // Controlla di nuovo se nel frattempo qualcuno ha generato lo stesso video
            $existing = $this->campaign->findExistingVideo();
            if ($existing && $existing->id !== $this->campaign->id) {
                $this->campaign->reuseVideoFrom($existing);
                Log::info('Video reused from parallel job', [
                    'campaign_id' => $this->campaign->id,
                    'reused_from' => $existing->id,
                ]);
                SendCampaignEmailJob::dispatch($this->campaign);
                return;
            }

            // Genera il video
            $s3Path = $videoService->concatenateForCampaign($this->campaign);

            $this->campaign->update([
                'video_path' => $s3Path,
                'video_status' => 'ready',
            ]);

            Log::info('Video generated successfully', [
                'campaign_id' => $this->campaign->id,
                'video_path' => $s3Path,
            ]);

            // Dispatcha job per invio email
            SendCampaignEmailJob::dispatch($this->campaign);

        } catch (\Exception $e) {
            $this->campaign->update(['video_status' => 'failed']);

            Log::error('Video generation failed', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
