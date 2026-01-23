<?php

namespace App\Filament\Resources\VideoSegments\Pages;

use App\Filament\Resources\VideoSegments\VideoSegmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVideoSegment extends EditRecord
{
    protected static string $resource = VideoSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
