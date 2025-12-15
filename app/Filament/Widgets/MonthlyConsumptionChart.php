<?php

namespace App\Filament\Widgets;

use App\Models\MeterReading;
use App\Models\ElectricMeter;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class MonthlyConsumptionChart extends ChartWidget
{
    protected ?string $heading = 'Tiêu thụ điện theo tháng';

    protected ?string $pollingInterval = '60s';

    protected int | string | array $columnSpan = 2;
    
    protected ?string $maxHeight = '250px';
    
    protected static ?int $sort = 6;
    
    public ?string $filter = null;
    
    public function mount(): void
    {
        $this->filter = (string) now()->year;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $year = (int) ($this->filter ?? now()->year);
        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $consumption = $this->calculateMonthlyConsumption($month, $year);
            $monthlyData[] = round($consumption, 2);
        }

        return [
            'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            'datasets' => [
                [
                    'label' => 'Điện năng tiêu thụ (kWh)',
                    'data' => $monthlyData,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(236, 72, 153, 0.7)',
                        'rgba(14, 165, 233, 0.7)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(251, 146, 60, 0.7)',
                        'rgba(249, 115, 22, 0.7)',
                        'rgba(168, 85, 247, 0.7)',
                        'rgba(217, 70, 239, 0.7)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                        'rgb(14, 165, 233)',
                        'rgb(34, 197, 94)',
                        'rgb(251, 146, 60)',
                        'rgb(249, 115, 22)',
                        'rgb(168, 85, 247)',
                        'rgb(217, 70, 239)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    private function calculateMonthlyConsumption(int $month, int $year): float
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $totalConsumption = 0;
        $meters = ElectricMeter::where('status', 'ACTIVE')->pluck('id');
        
        foreach ($meters as $meterId) {
            $current = MeterReading::where('electric_meter_id', $meterId)
                ->whereBetween('reading_date', [$startDate, $endDate])
                ->orderBy('reading_date', 'desc')
                ->first();
            
            if ($current) {
                $previous = MeterReading::where('electric_meter_id', $meterId)
                    ->where('reading_date', '<', $current->reading_date)
                    ->orderBy('reading_date', 'desc')
                    ->first();
                
                if ($previous) {
                    $meter = ElectricMeter::find($meterId);
                    $consumption = ($current->reading_value - $previous->reading_value) * ($meter->hsn ?? 1);
                    
                    if ($consumption > 0) {
                        $totalConsumption += $consumption;
                    }
                }
            }
        }
        
        return $totalConsumption;
    }

    protected function getFilters(): ?array
    {
        $currentYear = now()->year;
        $years = [];
        
        for ($i = $currentYear - 3; $i <= $currentYear; $i++) {
            $years[$i] = "Năm $i";
        }
        
        return $years;
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
