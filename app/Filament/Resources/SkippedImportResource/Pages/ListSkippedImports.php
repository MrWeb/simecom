<?php

namespace App\Filament\Resources\SkippedImportResource\Pages;

use App\Filament\Resources\SkippedImportResource;
use Filament\Resources\Pages\ListRecords;

class ListSkippedImports extends ListRecords
{
    protected static string $resource = SkippedImportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
