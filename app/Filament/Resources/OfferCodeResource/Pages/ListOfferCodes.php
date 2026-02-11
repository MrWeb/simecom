<?php

namespace App\Filament\Resources\OfferCodeResource\Pages;

use App\Filament\Resources\OfferCodeResource;
use App\Jobs\ImportOfferCodesJob;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListOfferCodes extends ListRecords
{
    protected static string $resource = OfferCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Importa Codici')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel/CSV')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required()
                        ->disk('local')
                        ->directory('imports')
                        ->visibility('private'),
                ])
                ->action(function (array $data) {
                    $path = $data['file'];

                    ImportOfferCodesJob::dispatch($path, auth()->id())
                        ->onQueue('imports')
                        ->delay(now()->addSeconds(10));

                    Notification::make()
                        ->title('Importazione codici offerta in corso...')
                        ->body('Il file è stato caricato. Riceverai una notifica al termine.')
                        ->icon('heroicon-o-arrow-path')
                        ->info()
                        ->persistent()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
