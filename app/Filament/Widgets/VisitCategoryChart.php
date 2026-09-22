<?php

namespace App\Filament\Widgets;

use App\Models\ActivityCategory;
use App\Models\VisitReport;
use Filament\Widgets\ChartWidget;

class VisitCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Funnel Aktivitas (Approaching / Dealing / Aftersales)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $categories = ActivityCategory::withCount('visitReports')->get();

        $labels = $categories->pluck('name')->toArray();
        $counts = $categories->pluck('visit_reports_count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Laporan',
                    'data' => $counts,
                    'backgroundColor' => [
                        '#3B82F6', // Blue for Approaching
                        '#F59E0B', // Amber for Dealing
                        '#10B981', // Emerald for Aftersales
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
