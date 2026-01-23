<?php

namespace App\Filament\Resources\VideoSegments\Pages;

use App\Filament\Resources\VideoSegments\VideoSegmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVideoSegments extends ListRecords
{
    protected static string $resource = VideoSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
