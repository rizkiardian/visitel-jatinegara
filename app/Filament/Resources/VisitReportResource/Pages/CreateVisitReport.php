<?php

namespace App\Filament\Resources\VisitReportResource\Pages;

use App\Filament\Resources\VisitReportResource;
use App\Models\ReportLocation;
use App\Models\ReportPhoto;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVisitReport extends CreateRecord
{
    protected static string $resource = VisitReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['validation_status'] = 'Pending';
        $data['validator_id'] = null;
        $data['validation_notes'] = null;
        $data['validated_at'] = null;

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getRawState();

        // 1. Simpan Lokasi GPS jika ada
        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            ReportLocation::create([
                'visit_report_id' => $this->record->id,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'accuracy_meters' => $data['accuracy_meters'] ?? null,
                'captured_at' => now(),
            ]);
        }

        // 2. Simpan Foto Lokasi / Gedung jika ada
        if (!empty($data['photo_location'])) {
            $locationPhotos = is_array($data['photo_location']) ? $data['photo_location'] : [$data['photo_location']];
            foreach ($locationPhotos as $fileUrl) {
                if (is_string($fileUrl) && !empty($fileUrl)) {
                    ReportPhoto::create([
                        'visit_report_id' => $this->record->id,
                        'photo_type' => 'LocationPhoto',
                        'file_url' => $fileUrl,
                        'latitude' => $data['latitude'] ?? null,
                        'longitude' => $data['longitude'] ?? null,
                        'uploaded_at' => now(),
                    ]);
                }
            }
        }

        // 3. Simpan Foto Bersama PIC jika ada
        if (!empty($data['photo_pic'])) {
            $picPhotos = is_array($data['photo_pic']) ? $data['photo_pic'] : [$data['photo_pic']];
            foreach ($picPhotos as $fileUrl) {
                if (is_string($fileUrl) && !empty($fileUrl)) {
                    ReportPhoto::create([
                        'visit_report_id' => $this->record->id,
                        'photo_type' => 'PhotoWithPIC',
                        'file_url' => $fileUrl,
                        'latitude' => $data['latitude'] ?? null,
                        'longitude' => $data['longitude'] ?? null,
                        'uploaded_at' => now(),
                    ]);
                }
            }
        }
    }
}
