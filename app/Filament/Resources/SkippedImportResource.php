<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SkippedImportResource\Pages;
use App\Models\OfferCode;
use App\Models\SkippedImport;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Forms\Components;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class SkippedImportResource extends Resource
{
    protected static ?string $model = SkippedImport::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Import Scartati';

    protected static ?string $modelLabel = 'Import Scartato';

    protected static ?string $pluralModelLabel = 'Import Scartati';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(8)
            ->components([
                Section::make('Dati Cliente')
                    ->schema([
                        Components\TextInput::make('email')
                            ->email()
                            ->nullable()
                            ->label('Email'),
                        Components\TextInput::make('phone')
                            ->label('Telefono'),
                        Components\TextInput::make('customer_name')
                            ->label('Nome Cliente'),
                        Components\Select::make('offer_code')
                            ->label('Codice Offerta')
                            ->options(function ($record) {
                                $options = OfferCode::where('active', true)
                                    ->get()
                                    ->mapWithKeys(fn ($offer) => [
                                        $offer->code => $offer->code . ' - ' . $offer->offer_name
                                    ])
                                    ->toArray();

                                // Includi il valore attuale se non è tra le options
                                if ($record?->offer_code && !isset($options[$record->offer_code])) {
                                    $options[$record->offer_code] = $record->offer_code . ' (non trovato)';
                                }

                                return $options;
                            })
                            ->searchable(),
                    ])
                    ->columnSpan(5),
                Section::make('Informazioni Import')
                    ->columnSpan(3)
                    ->schema([
                        Components\TextInput::make('source_file')
                            ->label('File Sorgente')
                            ->disabled(),
                        Components\TextInput::make('row_number')
                            ->label('Riga')
                            ->disabled(),
                        Components\TextInput::make('error_type')
                            ->label('Tipo Errore')
                            ->disabled(),
                        Components\TextInput::make('status')
                            ->label('Stato')
                            ->disabled(),
                    ]),
                Section::make('Dati Originali')
                    ->schema([
                        Components\KeyValue::make('row_data')
                            ->label('Dati Riga CSV')
                            ->disabled(),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Mancante'),
                TextColumn::make('phone')
                    ->label('Telefono')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Mancante'),
                TextColumn::make('offer_code')
                    ->label('Codice Offerta')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                TextColumn::make('error_type')
                    ->label('Errore')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'missing_email' => 'danger',
                        'missing_contact' => 'danger',
                        'missing_offer_code' => 'warning',
                        'missing_attachment' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'missing_email' => 'Email mancante',
                        'missing_contact' => 'Email e telefono mancanti',
                        'missing_offer_code' => 'Codice offerta non trovato',
                        'missing_attachment' => 'PDF allegato non trovato',
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processed' => 'success',
                        'ignored' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'In attesa',
                        'processed' => 'Elaborato',
                        'ignored' => 'Ignorato',
                        default => $state,
                    }),
                TextColumn::make('source_file')
                    ->label('File')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('error_type')
                    ->label('Tipo Errore')
                    ->options([
                        'missing_email' => 'Email mancante',
                        'missing_contact' => 'Email e telefono mancanti',
                        'missing_offer_code' => 'Codice offerta non trovato',
                        'missing_attachment' => 'PDF allegato non trovato',
                    ]),
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'pending' => 'In attesa',
                        'processed' => 'Elaborato',
                        'ignored' => 'Ignorato',
                    ]),
                SelectFilter::make('source_file')
                    ->label('File')
                    ->options(fn () => SkippedImport::distinct()->pluck('source_file', 'source_file')->toArray()),
            ])
            ->actions([
                Action::make('process')
                    ->label('Elabora')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Elabora e Invia')
                    ->modalDescription('Sei sicuro di voler elaborare questo record e inviare la campagna video?')
                    ->visible(fn (SkippedImport $record): bool => $record->status === 'pending')
                    ->action(function (SkippedImport $record): void {
                        // Deve avere almeno email o telefono
                        if (empty($record->email) && empty($record->phone)) {
                            Notification::make()
                                ->title('Errore')
                                ->body('Email o telefono mancante. Modifica il record prima di elaborare.')
                                ->danger()
                                ->send();
                            return;
                        }

                        if (!OfferCode::findByCode($record->offer_code)) {
                            Notification::make()
                                ->title('Errore')
                                ->body('Codice offerta non valido. Modifica il record prima di elaborare.')
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
                        } else {
                            $errorMsg = $record->error_type === 'missing_attachment'
                                ? 'PDF allegato non trovato.'
                                : 'Impossibile elaborare il record.';
                            Notification::make()
                                ->title('Errore')
                                ->body($errorMsg)
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('ignore')
                    ->label('Ignora')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Ignora Record')
                    ->modalDescription('Sei sicuro di voler ignorare questo record?')
                    ->visible(fn (SkippedImport $record): bool => $record->status === 'pending')
                    ->action(function (SkippedImport $record): void {
                        $record->update(['status' => 'ignored']);
                        Notification::make()
                            ->title('Record ignorato')
                            ->success()
                            ->send();
                    }),
                Action::make('edit')
                    ->label('Modifica')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->visible(fn (SkippedImport $record): bool => $record->status === 'pending')
                    ->url(fn (SkippedImport $record): string => static::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([
                BulkAction::make('process_bulk')
                    ->label('Elabora selezionati')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Elabora Record Selezionati')
                    ->modalDescription('Sei sicuro di voler elaborare tutti i record selezionati?')
                    ->action(function (Collection $records): void {
                        $processed = 0;
                        $failed = 0;

                        foreach ($records as $record) {
                            if ($record->status !== 'pending') {
                                continue;
                            }

                            // Deve avere almeno email o telefono
                            if ((empty($record->email) && empty($record->phone)) || !OfferCode::findByCode($record->offer_code)) {
                                $failed++;
                                continue;
                            }

                            $campaign = $record->retry();
                            if ($campaign) {
                                $processed++;
                            } else {
                                $failed++;
                            }
                        }

                        Notification::make()
                            ->title('Elaborazione completata')
                            ->body("Elaborati: {$processed}, Falliti: {$failed}")
                            ->success()
                            ->send();
                    }),
                BulkAction::make('ignore_bulk')
                    ->label('Ignora selezionati')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Ignora Record Selezionati')
                    ->modalDescription('Sei sicuro di voler ignorare tutti i record selezionati?')
                    ->action(function (Collection $records): void {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->status === 'pending') {
                                $record->update(['status' => 'ignored']);
                                $count++;
                            }
                        }

                        Notification::make()
                            ->title('Record ignorati')
                            ->body("{$count} record ignorati.")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSkippedImports::route('/'),
            'edit' => Pages\EditSkippedImport::route('/{record}/edit'),
        ];
    }
}
