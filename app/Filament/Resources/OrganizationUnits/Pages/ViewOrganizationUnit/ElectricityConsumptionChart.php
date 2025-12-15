<?php

namespace App\Filament\Resources\OrganizationUnits\Pages\ViewOrganizationUnit;

use Filament\Widgets\ChartWidget;
use App\Models\MeterReading;
use App\Models\OrganizationUnit;
use Carbon\Carbon;
use Livewire\Attributes\Reactive;

class ElectricityConsumptionChart extends ChartWidget
{
    protected int | string | array $columnSpan = 1;
    
    public ?string $filter = null;
    
    #[Reactive]
    public ?OrganizationUnit $record = null;
    
    public function mount(): void
    {
        $this->filter = (string) now()->year;
    }
    
    public function getHeading(): ?string
    {
        $year = $this->filter ?? now()->year;
        return "Biểu đồ tiêu thụ điện năm {$year}";
    }

    protected function getData(): array
    {
        if (!$this->record) {
            return $this->getEmptyData();
        }
        
        $organization = $this->record;
        $year = (int) $this->filter;
        
        // Lấy tất cả meters của organization hoặc children
        $meterIds = [];
        
        if ($organization->type === 'UNIT') {
            // Lấy tất cả meters của consumers con
            $consumers = $organization->children()->where('type', 'CONSUMER')->get();
            foreach ($consumers as $consumer) {
                $meterIds = array_merge($meterIds, $consumer->electricMeters()->pluck('id')->toArray());
            }
        } else {
            // Lấy meters của chính consumer này
            $meterIds = $organization->electricMeters()->pluck('id')->toArray();
        }
        
        if (empty($meterIds)) {
            return $this->getEmptyData();
        }
        
        // Tính consumption cho mỗi tháng
        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            
            $totalConsumption = 0;
            
            foreach ($meterIds as $meterId) {
                // Lấy reading cuối tháng
                $current = MeterReading::where('electric_meter_id', $meterId)
                    ->whereBetween('reading_date', [$startDate, $endDate])
                    ->orderBy('reading_date', 'desc')
                    ->first();
                
                if ($current) {
                    // Lấy reading trước đó
                    $previous = MeterReading::where('electric_meter_id', $meterId)
                        ->where('reading_date', '<', $current->reading_date)
                        ->orderBy('reading_date', 'desc')
                        ->first();
                    
                    if ($previous) {
                        $meter = \App\Models\ElectricMeter::find($meterId);
                        $consumption = ($current->reading_value - $previous->reading_value) * ($meter->hsn ?? 1);
                        $totalConsumption += $consumption;
                    }
                }
            }
            
            $monthlyData[] = round($totalConsumption, 2);
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Điện năng tiêu thụ (kWh)',
                    'data' => $monthlyData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
    
    protected function getEmptyData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Điện năng tiêu thụ (kWh)',
                    'data' => array_fill(0, 12, 0),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
        ];
    }
}
