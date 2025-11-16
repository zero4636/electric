<?php

namespace App\Filament\Widgets;

use App\Models\ElectricMeter;
use App\Models\MeterReading;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ReadingCoverageChart extends ChartWidget
{
    protected ?string $heading = 'Tỷ lệ bao phủ đọc số (30 ngày)';

    protected ?string $pollingInterval = '60s';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $cutoffDate = now()->subDays(30);
        
        // Get IDs of meters with readings in last 30 days
        $metersWithReadings = MeterReading::query()
            ->where('reading_date', '>=', $cutoffDate)
            ->distinct()
            ->pluck('electric_meter_id')
            ->toArray();

        $totalMeters = ElectricMeter::where('status', 'ACTIVE')->count();
        $withReadings = count(array_intersect(
            $metersWithReadings,
            ElectricMeter::where('status', 'ACTIVE')->pluck('id')->toArray()
        ));
        $withoutReadings = $totalMeters - $withReadings;

        $percentCovered = $totalMeters > 0 
            ? round(($withReadings / $totalMeters) * 100, 1) 
            : 0;

        return [
            'labels' => [
                "Có đọc số ({$percentCovered}%)",
                'Chưa có đọc số',
            ],
            'datasets' => [
                [
                    'label' => 'Công tơ',
                    'data' => [$withReadings, $withoutReadings],
                    'backgroundColor' => [
                        '#22c55e', // green-500
                        '#ef4444', // red-500
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
