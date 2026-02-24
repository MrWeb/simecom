<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SendingAnalyticsChart;
use App\Models\VideoCampaign;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class SendingAnalytics extends Page implements HasTable
{
    use InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Analytics Invii';

    protected static ?string $title = 'Analytics per Data di Invio';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.sending-analytics';

    protected function getHeaderWidgets(): array
    {
        return [
            SendingAnalyticsChart::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                VideoCampaign::query()
                    ->fromSub(
                        DB::table('video_campaigns')
                            ->select(
                                DB::raw('DATE(created_at) as sending_date'),
                                DB::raw('COUNT(*) as total_campaigns'),
                                DB::raw('SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as total_opened'),
                                DB::raw('SUM(CASE WHEN video_watched_seconds > 0 THEN 1 ELSE 0 END) as total_watched'),
                                DB::raw('ROUND(AVG(CASE WHEN video_watched_seconds > 0 THEN video_watched_seconds ELSE NULL END), 0) as avg_watched_seconds'),
                                DB::raw('SUM(CASE WHEN video_completed = 1 THEN 1 ELSE 0 END) as total_completed'),
                                DB::raw('MIN(id) as id'),
                            )
                            ->groupBy(DB::raw('DATE(created_at)')),
                        'video_campaigns'
                    )
            )
            ->columns([
                TextColumn::make('sending_date')
                    ->label('Data Invio')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('d/m/Y'))
                    ->sortable(),
                TextColumn::make('total_campaigns')
                    ->label('Totale')
                    ->sortable(),
                TextColumn::make('total_opened')
                    ->label('Aperture')
                    ->sortable(),
                TextColumn::make('open_rate')
                    ->label('Tasso Apertura')
                    ->getStateUsing(function ($record): string {
                        if ($record->total_campaigns == 0) return '0%';
                        return round(($record->total_opened / $record->total_campaigns) * 100, 1) . '%';
                    }),
                TextColumn::make('total_watched')
                    ->label('Video Visti')
                    ->sortable(),
                TextColumn::make('avg_watched_seconds')
                    ->label('Media Secondi')
                    ->formatStateUsing(function ($state): string {
                        if (! $state) return '-';
                        $state = (int) $state;
                        $min = intdiv($state, 60);
                        $sec = $state % 60;
                        return $min > 0 ? "{$min}m {$sec}s" : "{$sec}s";
                    })
                    ->sortable(),
                TextColumn::make('total_completed')
                    ->label('Completamenti')
                    ->sortable(),
                TextColumn::make('completion_rate')
                    ->label('Tasso Completamento')
                    ->getStateUsing(function ($record): string {
                        if ($record->total_opened == 0) return '0%';
                        return round(($record->total_completed / $record->total_opened) * 100, 1) . '%';
                    }),
            ])
            ->defaultSort('sending_date', 'desc')
            ->paginated([10, 25, 50]);
    }
}
