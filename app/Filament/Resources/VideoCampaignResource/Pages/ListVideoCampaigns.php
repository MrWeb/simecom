<?php

namespace App\Filament\Resources\VideoCampaignResource\Pages;

use App\Exports\VideoCampaignsExport;
use App\Filament\Resources\VideoCampaignResource;
use App\Models\VideoCampaign;
use App\Services\CampaignImportSimulator;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListVideoCampaigns extends ListRecords
{
    protected static string $resource = VideoCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('esporta')
                ->label('Esporta')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('Esporta Campagne Video')
                ->modalDescription('Seleziona le date di invio da esportare.')
                ->modalSubmitActionLabel('Esporta Excel')
                ->form([
                    CheckboxList::make('dates')
                        ->label('Date di invio')
                        ->options(function () {
                            return $this->getAvailableSendDates();
                        })
                        ->columns(3)
                        ->required()
                        ->bulkToggleable(),
                ])
                ->action(function (array $data) {
                    $dates = $data['dates'];
                    $filename = 'campagne-video-' . now()->format('Y-m-d-His') . '.xlsx';

                    return Excel::download(new VideoCampaignsExport($dates), $filename);
                }),
            Action::make('simulaInvio')
                ->label('Simula invio')
                ->icon('heroicon-o-beaker')
                ->color('info')
                ->modalHeading('Simulazione importazione')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Chiudi')
                ->modalContent(function () {
                    $simulator = new CampaignImportSimulator();
                    $result = $simulator->simulateAll();

                    return view('filament.modals.simulation-result', [
                        'result' => $result,
                    ]);
                }),
        ];
    }

    protected function getAvailableSendDates(): array
    {
        $emailDates = VideoCampaign::whereNotNull('email_sent_at')
            ->selectRaw('DATE(email_sent_at) as send_date')
            ->distinct();

        $smsDates = VideoCampaign::whereNotNull('sms_sent_at')
            ->selectRaw('DATE(sms_sent_at) as send_date')
            ->distinct();

        $dates = $emailDates->union($smsDates)
            ->orderByDesc('send_date')
            ->limit(30)
            ->pluck('send_date')
            ->mapWithKeys(fn ($date) => [
                $date => \Carbon\Carbon::parse($date)->format('d/m/Y'),
            ])
            ->toArray();

        return $dates;
    }
}
