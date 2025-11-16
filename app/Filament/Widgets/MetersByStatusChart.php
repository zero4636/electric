<?php

namespace App\Filament\Widgets;

use App\Models\ElectricMeter;
use Filament\Widgets\ChartWidget;

class MetersByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Tình trạng công tơ';

    protected ?string $pollingInterval = '60s';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        // Get counts grouped by status
        $raw = ElectricMeter::query()
            ->selectRaw('COALESCE(status, "UNKNOWN") as status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        // Desired order and label/color mapping
        $order = ['ACTIVE', 'INACTIVE', 'UNKNOWN'];
        $labelsMap = [
            'ACTIVE' => 'Hoạt động',
            'INACTIVE' => 'Ngừng',
            'UNKNOWN' => 'Khác',
        ];
        $colorsMap = [
            'ACTIVE' => '#22c55e', // green-500
            'INACTIVE' => '#ef4444', // red-500
            'UNKNOWN' => '#94a3b8', // slate-400
        ];

        $labels = [];
        $data = [];
        $colors = [];

        // Use ordered keys first
        foreach ($order as $key) {
            if (isset($raw[$key])) {
                $labels[] = $labelsMap[$key] ?? $key;
                $data[] = (int) $raw[$key];
                $colors[] = $colorsMap[$key] ?? '#94a3b8';
                unset($raw[$key]);
            }
        }

        // Append any remaining unexpected statuses
        foreach ($raw as $key => $count) {
            $labels[] = $labelsMap[$key] ?? ucfirst(strtolower((string) $key));
            $data[] = (int) $count;
            $colors[] = $colorsMap[$key] ?? '#94a3b8';
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Công tơ',
                    'data' => $data,
                    'backgroundColor' => $colors,
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
