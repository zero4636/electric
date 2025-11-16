<?php

namespace App\Filament\Widgets;

use App\Models\OrganizationUnit;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ConsumersByUnitChart extends ChartWidget
{
    protected ?string $heading = 'Hộ tiêu thụ theo đơn vị';

    protected ?string $pollingInterval = '60s';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Get top 10 parent units by consumer count
        $data = OrganizationUnit::query()
            ->select('parents.name', DB::raw('COUNT(*) as consumer_count'))
            ->from('organization_units as consumers')
            ->join('organization_units as parents', 'consumers.parent_id', '=', 'parents.id')
            ->where('consumers.type', 'CONSUMER')
            ->whereNotNull('consumers.parent_id')
            ->groupBy('parents.id', 'parents.name')
            ->orderByDesc('consumer_count')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Số hộ tiêu thụ',
                    'data' => $data->pluck('consumer_count')->toArray(),
                    'backgroundColor' => '#10b981', // emerald-500
                    'borderColor' => '#059669', // emerald-600
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Horizontal bar
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
