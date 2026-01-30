<?php

namespace App\Filament\Widgets;

use App\Models\VideoCampaign;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CampaignStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Email Inviate', VideoCampaign::where('email_status', 'sent')->count())
                ->description('Clicca per vedere')
                ->descriptionIcon('heroicon-o-envelope')
                ->color('success')
                ->url(route('filament.admin.resources.video-campaigns.index', [
                    'filters' => ['email_status' => ['value' => 'sent']]
                ])),

            Stat::make('Email Fallite', VideoCampaign::where('email_status', 'failed')->count())
                ->description('Clicca per vedere')
                ->descriptionIcon('heroicon-o-envelope')
                ->color('danger')
                ->url(route('filament.admin.resources.video-campaigns.index', [
                    'filters' => ['email_status' => ['value' => 'failed']]
                ])),

            Stat::make('SMS Inviati', VideoCampaign::where('sms_status', 'sent')->count())
                ->description('Clicca per vedere')
                ->descriptionIcon('heroicon-o-device-phone-mobile')
                ->color('success')
                ->url(route('filament.admin.resources.video-campaigns.index', [
                    'filters' => ['sms_status' => ['value' => 'sent']]
                ])),

            Stat::make('SMS Falliti', VideoCampaign::where('sms_status', 'failed')->count())
                ->description('Clicca per vedere')
                ->descriptionIcon('heroicon-o-device-phone-mobile')
                ->color('danger')
                ->url(route('filament.admin.resources.video-campaigns.index', [
                    'filters' => ['sms_status' => ['value' => 'failed']]
                ])),
        ];
    }
}
