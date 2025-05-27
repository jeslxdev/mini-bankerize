<?php

namespace App\Filament\Resources\FailedRequestResource\Pages;

use App\Filament\Resources\FailedRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFailedRequests extends ListRecords
{
    protected static string $resource = FailedRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
