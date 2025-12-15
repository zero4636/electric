<?php

namespace App\Filament\Widgets;

use App\Models\ElectricMeter;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MetersBySubstationChart extends ChartWidget
{
    protected ?string $heading = 'Phân bổ công tơ theo trạm';

    protected int | string | array $columnSpan = 2;
    
    protected ?string $maxHeight = '250px';
    
    protected static ?int $sort = 10;

    protected ?string $pollingInterval = '60s';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Get top 10 substations by meter count
        $data = ElectricMeter::query()
            ->select('substations.name', DB::raw('COUNT(*) as meter_count'))
            ->join('substations', 'electric_meters.substation_id', '=', 'substations.id')
            ->groupBy('substations.id', 'substations.name')
            ->orderByDesc('meter_count')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Số công tơ',
                    'data' => $data->pluck('meter_count')->toArray(),
                    'backgroundColor' => '#3b82f6', // blue-500
                    'borderColor' => '#2563eb', // blue-600
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
