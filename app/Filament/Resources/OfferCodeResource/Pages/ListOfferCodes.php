<?php

namespace App\Filament\Resources\OfferCodeResource\Pages;

use App\Filament\Resources\OfferCodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOfferCodes extends ListRecords
{
    protected static string $resource = OfferCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
