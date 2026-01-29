<?php

namespace App\Services;

use App\Models\VideoCampaign;
use Illuminate\Support\Facades\Log;

class VideoService
{
    protected string $ffmpegPath;
    protected string $segmentsBasePath;
    protected string $generatedPath;
    protected string $dynamicPath;

    public function __construct()
    {
        $this->ffmpegPath = config('services.ffmpeg.ffmpeg_path', '/usr/bin/ffmpeg');
        $this->segmentsBasePath = storage_path('app/public/videos');
        $this->generatedPath = storage_path('app/public/videos/generated');
        $this->dynamicPath = storage_path('app/public/videos');
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
        $segmentPaths = $this->resolveSegmentPaths($campaign->video_combination, $videoType, $campaign->offer_name);

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

        // Rimuovi file concat temporaneo (@ per evitare errori se già cancellato da job parallelo)
        @unlink($concatListPath);

        return "videos/generated/{$outputFilename}";
    }

    protected function resolveSegmentPaths(array $slugs, string $videoType, ?string $offerName = null): array
    {
        $paths = [];

        foreach ($slugs as $slug) {
            // Gestione segmento dinamico
            if ($slug === '__DYNAMIC_OFFER__') {
                if ($offerName) {
                    $paths[] = $this->generateDynamicOfferVideo($offerName, $videoType);
                }
                continue;
            }

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

    public function generateDynamicOfferVideo(string $offerName, string $videoType): string
    {
        // Hash per caching basato su nome offerta + tipo
        $hash = md5($offerName . '_' . $videoType);
        $dynamicDir = "{$this->segmentsBasePath}/{$videoType}/dynamic";
        $outputPath = "{$dynamicDir}/{$hash}.mp4";

        // Se già esiste, riusa
        if (file_exists($outputPath)) {
            return $outputPath;
        }

        // Crea directory se non esiste
        if (!is_dir($dynamicDir)) {
            mkdir($dynamicDir, 0755, true);
        }

        // Genera con overlay
        $baseVideo = "{$this->segmentsBasePath}/{$videoType}/offerta.mp4";
        $this->generateOfferWithOverlay($baseVideo, $outputPath, $offerName);

        return $outputPath;
    }

    protected function generateOfferWithOverlay(string $inputPath, string $outputPath, string $text): void
    {
        $fontPath = config('services.ffmpeg.overlay_font');
        $fontSize = (int) config('services.ffmpeg.overlay_fontsize');
        $lineHeight = 160; // Spaziatura tra le righe

        // Rimuovi newline/carriage return esistenti e sanitizza il testo
        $cleanText = str_replace(["\r\n", "\r", "\n"], " ", $text);
        $cleanText = trim(preg_replace('/\s+/', ' ', $cleanText));

        // Dividi in parole
        $words = explode(' ', $cleanText);
        $totalLines = count($words);

        // Calcola posizione Y iniziale per centrare verticalmente
        $startY = (1080 / 2) - (($totalLines - 1) * $lineHeight / 2);

        // Costruisci i filtri drawtext per ogni parola
        $drawTextFilters = [];
        foreach ($words as $index => $word) {
            $escapedWord = str_replace(["'", ":"], ["'\\''", "\\:"], $word);
            $yPos = $startY + ($index * $lineHeight);
            $drawTextFilters[] = sprintf(
                "drawtext=fontfile=%s:text='%s':fontsize=%d:fontcolor=0xFEB51E:borderw=5:bordercolor=white:x=w*0.45:y=%d:alpha='min(min(1\\,t/0.5)\\,min(1\\,(6.67-t)/0.5))'",
                escapeshellarg($fontPath),
                $escapedWord,
                $fontSize,
                (int) $yPos
            );
        }

        $vf = "fps=25,format=yuv420p," . implode(',', $drawTextFilters);

        $command = sprintf(
            '%s -i %s -vf "%s" -c:v libx264 -profile:v main -level 4.1 -g 25 -preset fast -crf 23 -video_track_timescale 25000 -c:a aac -b:a 128k -ar 48000 %s -y 2>&1',
            escapeshellarg($this->ffmpegPath),
            escapeshellarg($inputPath),
            $vf,
            escapeshellarg($outputPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::error('FFMPEG overlay generation failed', [
                'command' => $command,
                'output' => implode("\n", $output),
            ]);
            throw new \RuntimeException('FFMPEG overlay generation failed');
        }

        Log::info('Dynamic offer video generated', ['output' => $outputPath, 'text' => $text]);
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
