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
                Components\TextInput::make('code')
                    ->label('Codice')
                    ->required()
                    ->unique(ignoreRecord: true),
                Components\TextInput::make('offer_name')
                    ->label('Nome Offerta'),
                Components\Select::make('video_segment')
                    ->label('Segmento Video')
                    ->options([
                        'offerta-1' => 'Offerta 1',
                        'offerta-2' => 'Offerta 2',
                        'offerta-3' => 'Offerta 3',
                        'offerta-4' => 'Offerta 4',
                        'offerta-5' => 'Offerta 5',
                        'offerta-6' => 'Offerta 6',
                        'offerta-7' => 'Offerta 7',
                    ])
                    ->required(),
                Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'luce' => 'Luce',
                        'gas' => 'Gas',
                    ])
                    ->required()
                    ->default('luce'),
                Components\Toggle::make('active')
                    ->label('Attivo')
                    ->default(true),
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
                        'offerta-1' => 'Offerta 1',
                        'offerta-2' => 'Offerta 2',
                        'offerta-3' => 'Offerta 3',
                        'offerta-4' => 'Offerta 4',
                        'offerta-5' => 'Offerta 5',
                        'offerta-6' => 'Offerta 6',
                        'offerta-7' => 'Offerta 7',
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
