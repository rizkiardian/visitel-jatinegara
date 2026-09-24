<?php

namespace App\Filament\Resources\BusinessCustomerResource\Pages;

use App\Filament\Resources\BusinessCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessCustomer extends CreateRecord
{
    protected static string $resource = BusinessCustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()?->role !== 'Admin' && auth()->user()?->employee_id) {
            $data['employee_id'] = auth()->user()->employee_id;
        }

        return $data;
    }
}
