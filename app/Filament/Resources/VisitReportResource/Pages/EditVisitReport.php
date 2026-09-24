<?php

namespace App\Filament\Resources\VisitReportResource\Pages;

use App\Filament\Resources\VisitReportResource;
use App\Models\ReportLocation;
use App\Models\ReportPhoto;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVisitReport extends EditRecord
{
    protected static string $resource = VisitReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 1. Muat koordinat GPS terakhir
        $latestLocation = $this->record->locations()->latest('id')->first();
        if ($latestLocation) {
            $data['latitude'] = $latestLocation->latitude;
            $data['longitude'] = $latestLocation->longitude;
            $data['accuracy_meters'] = $latestLocation->accuracy_meters;
        }

        // 2. Muat foto lokasi dan foto PIC
        $photoLocation = $this->record->photos()->where('photo_type', 'LocationPhoto')->latest('id')->first();
        if ($photoLocation) {
            $data['photo_location'] = $photoLocation->file_url;
        }

        $photoPic = $this->record->photos()->where('photo_type', 'PhotoWithPIC')->latest('id')->first();
        if ($photoPic) {
            $data['photo_pic'] = $photoPic->file_url;
        }

        // 3. Muat info NIPNAS & Status BC
        if ($this->record->businessCustomer) {
            $data['nipnas'] = $this->record->businessCustomer->nipnas;
            $data['bc_status'] = $this->record->businessCustomer->status;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->role === 'AM') {
            unset($data['validation_status'], $data['validator_id'], $data['validation_notes'], $data['validated_at']);
        } elseif (!empty($data['validation_status']) && in_array($data['validation_status'], ['Valid', 'Rejected'])) {
            $data['validated_at'] = now();
            if (empty($data['validator_id']) && auth()->user()?->employee_id) {
                $data['validator_id'] = auth()->user()->employee_id;
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getRawState();

        // 1. Update / create Lokasi GPS
        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $location = $this->record->locations()->latest('id')->first();
            if ($location) {
                $location->update([
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'accuracy_meters' => $data['accuracy_meters'] ?? null,
                    'captured_at' => now(),
                ]);
            } else {
                ReportLocation::create([
                    'visit_report_id' => $this->record->id,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'accuracy_meters' => $data['accuracy_meters'] ?? null,
                    'captured_at' => now(),
                ]);
            }
        }

        // 2. Update / create Foto Lokasi
        if (!empty($data['photo_location'])) {
            $locationPhotos = is_array($data['photo_location']) ? $data['photo_location'] : [$data['photo_location']];
            $fileUrl = reset($locationPhotos);
            if (is_string($fileUrl) && !empty($fileUrl)) {
                ReportPhoto::updateOrCreate(
                    [
                        'visit_report_id' => $this->record->id,
                        'photo_type' => 'LocationPhoto',
                    ],
                    [
                        'file_url' => $fileUrl,
                        'latitude' => $data['latitude'] ?? null,
                        'longitude' => $data['longitude'] ?? null,
                        'uploaded_at' => now(),
                    ]
                );
            }
        }

        // 3. Update / create Foto PIC
        if (!empty($data['photo_pic'])) {
            $picPhotos = is_array($data['photo_pic']) ? $data['photo_pic'] : [$data['photo_pic']];
            $fileUrl = reset($picPhotos);
            if (is_string($fileUrl) && !empty($fileUrl)) {
                ReportPhoto::updateOrCreate(
                    [
                        'visit_report_id' => $this->record->id,
                        'photo_type' => 'PhotoWithPIC',
                    ],
                    [
                        'file_url' => $fileUrl,
                        'latitude' => $data['latitude'] ?? null,
                        'longitude' => $data['longitude'] ?? null,
                        'uploaded_at' => now(),
                    ]
                );
            }
        }
    }
}
