<?php

namespace App\Filament\Resources\TeldaResource\Pages;

use App\Filament\Resources\TeldaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTelda extends EditRecord
{
    protected static string $resource = TeldaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
