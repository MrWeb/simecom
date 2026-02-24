<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SendingAnalyticsChart extends ChartWidget
{
    protected ?string $heading = 'Panoramica Invii';

    protected ?string $maxHeight = '400px';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'responsive' => true,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $data = DB::table('video_campaigns')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened'),
                DB::raw('SUM(CASE WHEN video_watched_seconds > 0 THEN 1 ELSE 0 END) as watched'),
                DB::raw('SUM(CASE WHEN video_completed = 1 THEN 1 ELSE 0 END) as completed'),
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->limit(30)
            ->get();

        $labels = $data->map(fn ($row) => Carbon::parse($row->date)->format('d/m'))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Inviate',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Aperte',
                    'data' => $data->pluck('opened')->toArray(),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.7)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Video Visti',
                    'data' => $data->pluck('watched')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.7)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Completati',
                    'data' => $data->pluck('completed')->toArray(),
                    'backgroundColor' => 'rgba(139, 92, 246, 0.7)',
                    'borderColor' => 'rgb(139, 92, 246)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
