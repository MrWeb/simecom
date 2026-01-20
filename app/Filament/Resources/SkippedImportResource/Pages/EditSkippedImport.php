<?php

namespace App\Filament\Resources\SkippedImportResource\Pages;

use App\Filament\Resources\SkippedImportResource;
use App\Models\OfferCode;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSkippedImport extends EditRecord
{
    protected static string $resource = SkippedImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('process')
                ->label('Elabora e Invia')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Elabora e Invia')
                ->modalDescription('Vuoi salvare le modifiche, elaborare il record e inviare la campagna video?')
                ->visible(fn () => $this->record->status === 'pending')
                ->action(function (): void {
                    // Prima salva le modifiche
                    $this->save();

                    $record = $this->record->fresh();

                    if (empty($record->email)) {
                        Notification::make()
                            ->title('Errore')
                            ->body('Email mancante.')
                            ->danger()
                            ->send();
                        return;
                    }

                    if (!OfferCode::findByCode($record->offer_code)) {
                        Notification::make()
                            ->title('Errore')
                            ->body('Codice offerta non valido.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $campaign = $record->retry();

                    if ($campaign) {
                        Notification::make()
                            ->title('Successo')
                            ->body('Campagna creata e video in elaborazione.')
                            ->success()
                            ->send();

                        $this->redirect(SkippedImportResource::getUrl('index'));
                    } else {
                        Notification::make()
                            ->title('Errore')
                            ->body('Impossibile elaborare il record.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Salva');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Annulla');
    }

    protected function getRedirectUrl(): ?string
    {
        return null; // Rimane sulla stessa pagina
    }
}
