<?php

namespace App\Filament\Resources\VideoCampaignResource\Pages;

use App\Filament\Resources\VideoCampaignResource;
use Filament\Resources\Pages\ListRecords;

class ListVideoCampaigns extends ListRecords
{
    protected static string $resource = VideoCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
