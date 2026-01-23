<?php

namespace App\Filament\Resources\VideoSegments\Schemas;

use App\Models\VideoSegment;
use Filament\Forms\Components;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VideoSegmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informazioni Video')
                    ->description('Nome e identificativo del segmento video')
                    ->icon('heroicon-o-film')
                    ->columns(2)
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Nome')
                            ->placeholder('Es: SOS Bee')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $record) {
                                // Genera slug solo se è nuovo record
                                if (!$record) {
                                    $set('slug', 'offerta-' . Str::slug($state));
                                }
                            })
                            ->prefixIcon('heroicon-o-tag'),
                        Components\TextInput::make('slug')
                            ->label('Slug')
                            ->placeholder('Es: offerta-sos-bee')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, callable $get) {
                                return $rule->where('type', $get('type'));
                            })
                            ->helperText('Identificativo unico usato nel codice. Deve iniziare con "offerta-" per le offerte.')
                            ->prefixIcon('heroicon-o-link'),
                        Components\Select::make('type')
                            ->label('Tipologia')
                            ->options([
                                'luce' => 'Luce',
                                'gas' => 'Gas',
                            ])
                            ->required()
                            ->default('luce')
                            ->native(false)
                            ->prefixIcon('heroicon-o-bolt')
                            ->live(),
                    ]),

                Section::make('File Video')
                    ->description(fn ($record) => $record?->fileExists()
                        ? 'File attuale: ' . $record->filename . ' - Carica un nuovo file per sostituirlo'
                        : 'Carica il file video MP4'
                    )
                    ->icon(fn ($record) => $record?->fileExists() ? 'heroicon-o-check-circle' : 'heroicon-o-arrow-up-tray')
                    ->schema([
                        // Mostra info file attuale in edit
                        Components\Placeholder::make('current_file_info')
                            ->label('File Attuale')
                            ->content(fn ($record): string => $record?->fileExists()
                                ? "✓ {$record->filename} (presente in videos/{$record->type}/)"
                                : '✗ File non trovato'
                            )
                            ->visible(fn ($record): bool => $record !== null),

                        // Link per aprire il video attuale
                        Components\Actions::make([
                            Components\Actions\Action::make('view_video')
                                ->label('Apri Video')
                                ->icon('heroicon-o-play')
                                ->color('info')
                                ->url(fn ($record): string => $record?->getUrl() ?? '#')
                                ->openUrlInNewTab()
                                ->visible(fn ($record): bool => $record?->fileExists() ?? false),
                        ])->visible(fn ($record): bool => $record?->fileExists() ?? false),

                        Components\FileUpload::make('filename')
                            ->label(fn ($record) => $record ? 'Sostituisci Video' : 'Video')
                            ->disk('public')
                            ->directory(fn (callable $get) => 'videos/' . ($get('type') ?? 'luce'))
                            ->acceptedFileTypes(['video/mp4'])
                            ->maxSize(512000) // 500MB
                            ->required(fn ($record): bool => $record === null) // Required solo in creazione
                            ->downloadable()
                            ->openable()
                            ->previewable(false)
                            ->helperText(fn ($record) => $record
                                ? 'Carica un nuovo file MP4 per sostituire quello esistente. Lascia vuoto per mantenere il file attuale.'
                                : 'Formato accettato: MP4. Dimensione massima: 500MB.'
                            )
                            ->getUploadedFileNameForStorageUsing(function ($file, callable $get): string {
                                $slug = $get('slug') ?: 'video-' . time();
                                return $slug . '.mp4';
                            }),
                    ]),

                Section::make('Opzioni')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(2)
                    ->schema([
                        Components\Toggle::make('is_offer')
                            ->label('Segmento Offerta')
                            ->helperText('Attiva se questo video è selezionabile come offerta nei codici')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('gray')
                            ->inline(false),
                        Components\Toggle::make('active')
                            ->label('Attivo')
                            ->helperText('Se disattivato, il video non sarà disponibile')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ]),
            ]);
    }
}
