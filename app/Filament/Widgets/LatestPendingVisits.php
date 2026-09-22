<?php

namespace App\Filament\Widgets;

use App\Models\VisitReport;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPendingVisits extends BaseWidget
{
    protected static ?string $heading = 'Laporan Kunjungan Menunggu Validasi';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                VisitReport::query()
                    ->where('validation_status', 'Pending')
                    ->latest('visit_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('visit_date')
                    ->date('d M Y')
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Account Manager'),
                Tables\Columns\TextColumn::make('businessCustomer.name')
                    ->label('Pelanggan (BC)'),
                Tables\Columns\TextColumn::make('activityCategory.name')
                    ->badge()
                    ->color('info')
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->money('IDR', locale: 'id_ID')
                    ->label('Estimasi Nilai'),
                Tables\Columns\TextColumn::make('validation_status')
                    ->badge()
                    ->color('warning')
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn(VisitReport $record) => $record->update([
                        'validation_status' => 'Valid',
                        'validated_at' => now(),
                    ])),
            ]);
    }
}
