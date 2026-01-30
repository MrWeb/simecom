<?php

namespace App\Filament\Pages;

use App\Models\VideoCampaign;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\CampaignStatsWidget::class,
        ];
    }
}
