<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoCampaignResource\Pages;
use App\Jobs\SendCampaignEmailJob;
use App\Models\VideoCampaign;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Forms\Components;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VideoCampaignResource extends Resource
{
    protected static ?string $model = VideoCampaign::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Campagne Video';

    protected static ?string $modelLabel = 'Campagna Video';

    protected static ?string $pluralModelLabel = 'Campagne Video';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Components\TextInput::make('customer_name')
                    ->required(),
                Components\Select::make('video_status')
                    ->options([
                        'pending' => 'In attesa',
                        'processing' => 'In elaborazione',
                        'ready' => 'Pronto',
                        'failed' => 'Fallito',
                    ])
                    ->disabled(),
                Components\Select::make('email_status')
                    ->options([
                        'pending' => 'Non inviata',
                        'sent' => 'Inviata',
                        'failed' => 'Fallita',
                    ])
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('video_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'luce' => 'warning',
                        'gas' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                IconColumn::make('offer_code')
                    ->label('Cod.')
                    ->icon('heroicon-o-hashtag')
                    ->color('gray')
                    ->tooltip(fn (VideoCampaign $record): ?string => $record->offer_code),
                IconColumn::make('offer_name')
                    ->label('Offerta')
                    ->icon('heroicon-o-tag')
                    ->color('gray')
                    ->tooltip(fn (VideoCampaign $record): ?string => $record->offer_name),
                TextColumn::make('video_status')
                    ->label('Video')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'ready' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'In attesa',
                        'processing' => 'In elaborazione',
                        'ready' => 'Pronto',
                        'failed' => 'Fallito',
                        default => $state,
                    }),
                TextColumn::make('email_status')
                    ->label('Email')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Non inviata',
                        'sent' => 'Inviata',
                        'failed' => 'Fallita',
                        default => $state,
                    }),
                TextColumn::make('email_sent_at')
                    ->label('Inviata il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->label('Aperta il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Mai'),
            ])
            ->filters([
                SelectFilter::make('video_status')
                    ->label('Stato Video')
                    ->options([
                        'pending' => 'In attesa',
                        'processing' => 'In elaborazione',
                        'ready' => 'Pronto',
                        'failed' => 'Fallito',
                    ]),
                SelectFilter::make('email_status')
                    ->label('Stato Email')
                    ->options([
                        'pending' => 'Non inviata',
                        'sent' => 'Inviata',
                        'failed' => 'Fallita',
                    ]),
            ])
            ->actions([
                Action::make('resend')
                    ->label('Rispedisci')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reinvia email')
                    ->modalDescription('Sei sicuro di voler reinviare l\'email a questo cliente?')
                    ->visible(fn (VideoCampaign $record): bool => $record->video_status === 'ready')
                    ->action(function (VideoCampaign $record): void {
                        $record->update(['email_status' => 'pending']);
                        SendCampaignEmailJob::dispatch($record);
                        Notification::make()
                            ->title('Email in coda')
                            ->body('L\'email verrà reinviata a breve.')
                            ->success()
                            ->send();
                    }),
                Action::make('open_video')
                    ->label('Apri Video')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn (VideoCampaign $record): bool => $record->video_status === 'ready')
                    ->url(fn (VideoCampaign $record): string => $record->getLandingUrl())
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->recordUrl(null)
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideoCampaigns::route('/'),
            'view' => Pages\ViewVideoCampaign::route('/{record}'),
        ];
    }
}
