<?php

namespace App\Filament\Resources\TeldaResource\Pages;

use App\Filament\Resources\TeldaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeldas extends ListRecords
{
    protected static string $resource = TeldaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
