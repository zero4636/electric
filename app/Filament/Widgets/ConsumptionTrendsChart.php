<?php

namespace App\Filament\Widgets;

use App\Models\MeterReading;
use App\Models\ElectricMeter;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsumptionTrendsChart extends ChartWidget
{
    protected ?string $heading = 'Xu hướng tiêu thụ điện (30 ngày gần nhất)';

    protected ?string $pollingInterval = '60s';

    protected int | string | array $columnSpan = 2;
    
    protected ?string $maxHeight = '250px';
    
    protected static ?int $sort = 7;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = 30;
        $dates = [];
        $values = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dates[] = $date->format('d/m');
            
            // Tính tiêu thụ thực cho ngày này
            $consumption = $this->calculateDailyConsumption($date);
            $values[] = round($consumption, 2);
        }

        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Điện năng tiêu thụ (kWh)',
                    'data' => $values,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    private function calculateDailyConsumption(Carbon $date): float
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        $totalConsumption = 0;
        
        // Lấy tất cả readings trong ngày
        $readings = MeterReading::whereBetween('reading_date', [$startOfDay, $endOfDay])
            ->with('electricMeter')
            ->get();
        
        foreach ($readings as $reading) {
            // Tìm reading trước đó
            $previousReading = MeterReading::where('electric_meter_id', $reading->electric_meter_id)
                ->where('reading_date', '<', $reading->reading_date)
                ->orderBy('reading_date', 'desc')
                ->first();
            
            if ($previousReading) {
                $meter = $reading->electricMeter;
                $hsn = $meter ? $meter->hsn : 1;
                $consumption = ($reading->reading_value - $previousReading->reading_value) * $hsn;
                
                if ($consumption > 0) {
                    $totalConsumption += $consumption;
                }
            }
        }
        
        return $totalConsumption;
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
                        'callback' => 'function(value) { return value + " kWh"; }',
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
