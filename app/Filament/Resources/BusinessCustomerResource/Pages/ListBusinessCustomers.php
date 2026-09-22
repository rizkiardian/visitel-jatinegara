<?php

namespace App\Filament\Resources\BusinessCustomerResource\Pages;

use App\Filament\Resources\BusinessCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusinessCustomers extends ListRecords
{
    protected static string $resource = BusinessCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
