<?php

namespace App\Filament\Resources\VideoSegments;

use App\Filament\Resources\VideoSegments\Pages\CreateVideoSegment;
use App\Filament\Resources\VideoSegments\Pages\EditVideoSegment;
use App\Filament\Resources\VideoSegments\Pages\ListVideoSegments;
use App\Filament\Resources\VideoSegments\Schemas\VideoSegmentForm;
use App\Filament\Resources\VideoSegments\Tables\VideoSegmentsTable;
use App\Models\VideoSegment;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class VideoSegmentResource extends Resource
{
    protected static ?string $model = VideoSegment::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationLabel = 'Segmenti Video';

    protected static ?string $modelLabel = 'Segmento Video';

    protected static ?string $pluralModelLabel = 'Segmenti Video';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return VideoSegmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VideoSegmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVideoSegments::route('/'),
            'create' => CreateVideoSegment::route('/create'),
            'edit' => EditVideoSegment::route('/{record}/edit'),
        ];
    }
}
