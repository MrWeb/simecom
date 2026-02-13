<?php

namespace App\Filament\Resources\VideoCampaignResource\Pages;

use App\Filament\Resources\VideoCampaignResource;
use App\Services\CampaignImportSimulator;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListVideoCampaigns extends ListRecords
{
    protected static string $resource = VideoCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
}
