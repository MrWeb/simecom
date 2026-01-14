<?php

namespace App\Services;

use App\Models\VideoCampaign;
use Illuminate\Support\Facades\Log;

class VideoService
{
    protected string $ffmpegPath;
    protected string $segmentsBasePath;
    protected string $generatedPath;

    public function __construct()
    {
        $this->ffmpegPath = config('services.ffmpeg.ffmpeg_path', '/usr/bin/ffmpeg');
        $this->segmentsBasePath = storage_path('app/public/videos');
        $this->generatedPath = storage_path('app/public/videos/generated');
    }

    public function concatenateForCampaign(VideoCampaign $campaign): string
    {
        $outputFilename = "{$campaign->video_hash}.mp4";
        $outputPath = "{$this->generatedPath}/{$outputFilename}";

        // Se il video esiste già, ritorna il path
        if (file_exists($outputPath)) {
            return "videos/generated/{$outputFilename}";
        }

        // Trova i file dei segmenti
        $videoType = $campaign->video_type ?? 'luce';
        $segmentPaths = $this->resolveSegmentPaths($campaign->video_combination, $videoType);

        if (empty($segmentPaths)) {
            throw new \RuntimeException('No video segments found for combination');
        }

        // Crea file di concat per FFMPEG
        $concatListPath = "{$this->generatedPath}/concat_{$campaign->video_hash}.txt";
        $concatContent = collect($segmentPaths)
            ->map(fn($path) => "file '{$path}'")
            ->implode("\n");
        file_put_contents($concatListPath, $concatContent);

        // Concatena con FFMPEG
        $this->concatenateWithDemuxer($concatListPath, $outputPath);

        // Rimuovi file concat temporaneo
        unlink($concatListPath);

        return "videos/generated/{$outputFilename}";
    }

    protected function resolveSegmentPaths(array $slugs, string $videoType): array
    {
        $paths = [];

        foreach ($slugs as $slug) {
            $found = false;

            // Prova varie convenzioni di naming
            $possibleNames = [
                "{$slug}.mp4",
                str_replace('_', '-', $slug) . '.mp4',
                str_replace('-', '_', $slug) . '.mp4',
            ];

            foreach ($possibleNames as $filename) {
                $fullPath = "{$this->segmentsBasePath}/{$videoType}/{$filename}";
                if (file_exists($fullPath)) {
                    $paths[] = $fullPath;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                Log::warning("Video segment not found: {$slug}", ['type' => $videoType]);
            }
        }

        return $paths;
    }

    protected function concatenateWithDemuxer(string $concatListPath, string $outputPath): void
    {
        $command = sprintf(
            '%s -f concat -safe 0 -i %s -c copy %s -y 2>&1',
            escapeshellarg($this->ffmpegPath),
            escapeshellarg($concatListPath),
            escapeshellarg($outputPath)
        );

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::error('FFMPEG concat failed', [
                'command' => $command,
                'output' => implode("\n", $output),
                'return_code' => $returnCode,
            ]);
            throw new \RuntimeException('FFMPEG concatenation failed: ' . implode("\n", $output));
        }

        Log::info('Video concatenated successfully', ['output' => $outputPath]);
    }
}
