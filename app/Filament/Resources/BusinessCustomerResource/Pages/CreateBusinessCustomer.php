<?php

namespace App\Filament\Resources\BusinessCustomerResource\Pages;

use App\Filament\Resources\BusinessCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessCustomer extends CreateRecord
{
    protected static string $resource = BusinessCustomerResource::class;
}
