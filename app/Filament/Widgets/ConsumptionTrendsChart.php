<?php

namespace App\Filament\Widgets;

use App\Models\MeterReading;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ConsumptionTrendsChart extends ChartWidget
{
    protected ?string $heading = 'Tổng sản lượng tiêu thụ (30 ngày)';

    protected ?string $pollingInterval = '60s';

    protected int | string | array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = 30;
        $startDate = now()->subDays($days - 1)->startOfDay();
        
        // Get readings grouped by date with consumption calculation
        $readings = MeterReading::query()
            ->select(
                'reading_date',
                DB::raw('SUM(reading_value) as total_reading')
            )
            ->where('reading_date', '>=', $startDate)
            ->groupBy('reading_date')
            ->orderBy('reading_date')
            ->get()
            ->keyBy(fn($r) => $r->reading_date->format('Y-m-d'));

        // Generate all dates for the range
        $dates = [];
        $values = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $dates[] = $date->format('d/m');
            
            // For demo: use reading count as proxy for consumption
            // In real scenario: calculate delta from previous readings
            $count = isset($readings[$dateKey]) 
                ? MeterReading::whereDate('reading_date', $dateKey)->count()
                : 0;
            
            $values[] = $count;
        }

        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Số lượt đọc',
                    'data' => $values,
                    'borderColor' => '#f59e0b', // amber-500
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
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
