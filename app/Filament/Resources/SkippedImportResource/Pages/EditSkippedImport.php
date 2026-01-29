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

                    if (empty($record->email) && empty($record->phone)) {
                        Notification::make()
                            ->title('Errore')
                            ->body('Email o telefono mancante.')
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
                        $channel = !empty($record->email) ? 'email' : 'SMS';
                        Notification::make()
                            ->title('Successo')
                            ->body("Campagna creata e video in elaborazione. Notifica via {$channel}.")
                            ->success()
                            ->send();

                        $this->redirect(SkippedImportResource::getUrl('index'));
                    } else {
                        $errorMsg = $record->error_type === 'missing_attachment'
                            ? 'PDF allegato non trovato. Carica il file nella cartella pdf via FTP'
                            : 'Impossibile elaborare il record.';
                        Notification::make()
                            ->title('Errore')
                            ->body($errorMsg)
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
