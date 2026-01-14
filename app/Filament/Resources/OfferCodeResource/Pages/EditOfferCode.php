<?php

namespace App\Filament\Resources\OfferCodeResource\Pages;

use App\Filament\Resources\OfferCodeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOfferCode extends EditRecord
{
    protected static string $resource = OfferCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
