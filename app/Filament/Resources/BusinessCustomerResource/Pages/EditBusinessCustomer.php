<?php

namespace App\Filament\Resources\BusinessCustomerResource\Pages;

use App\Filament\Resources\BusinessCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusinessCustomer extends EditRecord
{
    protected static string $resource = BusinessCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
