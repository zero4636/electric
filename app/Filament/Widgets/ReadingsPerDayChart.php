<?php

namespace App\Filament\Widgets;

use App\Models\MeterReading;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ReadingsPerDayChart extends ChartWidget
{
    protected ?string $heading = 'Số lượt ghi chỉ số (30 ngày)';

    protected function getData(): array
    {
        $start = Carbon::now()->subDays(29)->startOfDay();
        $dates = collect(range(0, 29))
            ->map(fn ($i) => $start->copy()->addDays($i)->toDateString());

        $counts = MeterReading::query()
            ->where('reading_date', '>=', $start->toDateString())
            ->selectRaw('reading_date, COUNT(*) as c')
            ->groupBy('reading_date')
            ->pluck('c', 'reading_date');

        $series = $dates->map(fn ($d) => (int) ($counts[$d] ?? 0))->values();

        return [
            'datasets' => [
                [
                    'label' => 'Lượt ghi',
                    'data' => $series,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, .2)',
                    'tension' => 0.35,
                ],
            ],
            'labels' => $dates->map(fn ($d) => Carbon::parse($d)->format('d/m'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
