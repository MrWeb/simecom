<?php

namespace App\Filament\Resources\VideoSegments\Schemas;

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
                    ->description('Carica il file video MP4')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema([
                        Components\FileUpload::make('filename')
                            ->label('Video')
                            ->disk('public')
                            ->directory(fn (callable $get) => 'videos/' . ($get('type') ?? 'luce'))
                            ->acceptedFileTypes(['video/mp4'])
                            ->maxSize(512000) // 500MB
                            ->required()
                            ->downloadable()
                            ->openable()
                            ->previewable(false)
                            ->helperText('Formato accettato: MP4. Dimensione massima: 500MB.')
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
