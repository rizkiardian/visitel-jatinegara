<?php

namespace App\Filament\Widgets;

use App\Models\BusinessCustomer;
use App\Models\VisitReport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalVisits = VisitReport::count();
        $pendingVisits = VisitReport::where('validation_status', 'Pending')->count();
        $validVisits = VisitReport::where('validation_status', 'Valid')->count();
        $totalValue = VisitReport::sum('estimated_value');
        $totalBc = BusinessCustomer::count();

        return [
            Stat::make('Total Kunjungan', number_format($totalVisits))
                ->description('Rekap seluruh kunjungan BC')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Menunggu Validasi', number_format($pendingVisits))
                ->description('Perlu review atasan/supervisor')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Kunjungan Valid', number_format($validVisits))
                ->description('Telah disetujui supervisor')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Nilai Pipeline', 'Rp ' . number_format($totalValue, 0, ',', '.'))
                ->description('Estimasi nilai potensi bisnis')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Business Customer', number_format($totalBc))
                ->description('Total akun pelanggan tercatat')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('gray'),
        ];
    }
}
