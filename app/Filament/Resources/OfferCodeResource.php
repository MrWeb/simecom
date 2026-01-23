<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfferCodeResource\Pages;
use App\Models\OfferCode;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Forms\Components;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OfferCodeResource extends Resource
{
    protected static ?string $model = OfferCode::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Codici Offerta';

    protected static ?string $modelLabel = 'Codice Offerta';

    protected static ?string $pluralModelLabel = 'Codici Offerta';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informazioni Codice')
                    ->description('Dati identificativi del codice offerta')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        Components\TextInput::make('code')
                            ->label('Codice')
                            ->placeholder('Es: NV2508EDSPUN3FA')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->prefixIcon('heroicon-o-key'),
                        Components\TextInput::make('offer_name')
                            ->label('Nome Offerta')
                            ->placeholder('Es: SOS BEE CASA')
                            ->maxLength(100)
                            ->prefixIcon('heroicon-o-tag'),
                    ]),

                Section::make('Configurazione Video')
                    ->description('Associazione al segmento video e tipologia')
                    ->icon('heroicon-o-film')
                    ->columns(2)
                    ->schema([
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
                        Components\Select::make('video_segment')
                            ->label('Segmento Video')
                            ->options(fn (callable $get): array =>
                                match ($get('type')) {
                                    'gas' => [
                                        'offerta-sos-bee' => 'SOS Bee',
                                        'offerta-easy-click' => 'Easy Click',
                                        'offerta-green-planet' => 'Green Planet',
                                        'offerta-zero-rischi' => 'Zero Rischi',
                                        'offerta-turbo-green' => 'Turbo Green',
                                        'offerta-dinamica' => 'Dinamica',
                                        'offerta-seconda-casa' => 'Seconda Casa',
                                    ],
                                    default => [
                                        'offerta-sos-bee' => 'SOS Bee',
                                        'offerta-easy-click' => 'Easy Click',
                                        'offerta-prezzo-chiaro' => 'Prezzo Chiaro',
                                        'offerta-zero-rischi' => 'Zero Rischi',
                                        'offerta-led-collection' => 'Led Collection',
                                        'offerta-seguimi' => 'Seguimi',
                                        'offerta-seconda-casa' => 'Seconda Casa',
                                    ],
                                }
                            )
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-o-play-circle')
                            ->searchable(),
                    ]),

                Section::make('Stato')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Components\Toggle::make('active')
                            ->label('Codice Attivo')
                            ->helperText('Se disattivato, il codice non verrà riconosciuto durante l\'importazione')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Codice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('offer_name')
                    ->label('Nome Offerta')
                    ->searchable(),
                TextColumn::make('video_segment')
                    ->label('Video')
                    ->badge()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'luce' => 'warning',
                        'gas' => 'info',
                        default => 'gray',
                    }),
                IconColumn::make('active')
                    ->label('Attivo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('video_segment')
                    ->label('Segmento Video')
                    ->options([
                        'offerta-sos-bee' => 'SOS Bee',
                        'offerta-easy-click' => 'Easy Click',
                        'offerta-prezzo-chiaro' => 'Prezzo Chiaro (Luce)',
                        'offerta-green-planet' => 'Green Planet (Gas)',
                        'offerta-zero-rischi' => 'Zero Rischi',
                        'offerta-led-collection' => 'Led Collection (Luce)',
                        'offerta-turbo-green' => 'Turbo Green (Gas)',
                        'offerta-seguimi' => 'Seguimi (Luce)',
                        'offerta-dinamica' => 'Dinamica (Gas)',
                        'offerta-seconda-casa' => 'Seconda Casa',
                    ]),
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'luce' => 'Luce',
                        'gas' => 'Gas',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfferCodes::route('/'),
            'create' => Pages\CreateOfferCode::route('/create'),
            'edit' => Pages\EditOfferCode::route('/{record}/edit'),
        ];
    }
}
