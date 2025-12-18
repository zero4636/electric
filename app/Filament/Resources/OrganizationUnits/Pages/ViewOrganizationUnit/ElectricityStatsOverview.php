<?php

namespace App\Filament\Resources\OrganizationUnits\Pages\ViewOrganizationUnit;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\MeterReading;
use App\Models\OrganizationUnit;
use Carbon\Carbon;
use Livewire\Attributes\Reactive;

class ElectricityStatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 1;
    
    #[Reactive]
    public ?OrganizationUnit $record = null;
    
    protected function getColumns(): int
    {
        return 2; // Display stats in 2 columns (2x2 grid)
    }

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $organization = $this->record;
        $currentYear = now()->year;
        $currentMonth = now()->month;
        
        // Lấy tất cả meters
        $meterIds = $this->getMeterIds($organization);
        
        if (empty($meterIds)) {
            return [
                Stat::make('Tổng số công tơ', '0')
                    ->description('Chưa có công tơ nào')
                    ->descriptionIcon('heroicon-m-bolt')
                    ->color('gray'),
            ];
        }

        // Tính tiêu thụ tháng hiện tại (đặc biệt: nếu không có reading trước thì lấy current value)
        $currentMonthConsumption = $this->calculateCurrentMonthConsumption($meterIds, $currentMonth, $currentYear);
        
        // Tính tiêu thụ tháng trước
        $lastMonth = $currentMonth - 1;
        $lastMonthYear = $currentYear;
        if ($lastMonth < 1) {
            $lastMonth = 12;
            $lastMonthYear--;
        }
        $lastMonthConsumption = $this->calculateMonthlyConsumption($meterIds, $lastMonth, $lastMonthYear);
        
        // Tính % thay đổi
        $changePercent = 0;
        $trend = 'stable';
        $hasComparison = false;
        
        if ($lastMonthConsumption > 0 && $currentMonthConsumption > 0) {
            $hasComparison = true;
            $changePercent = (($currentMonthConsumption - $lastMonthConsumption) / $lastMonthConsumption) * 100;
            $trend = $changePercent > 0 ? 'increase' : ($changePercent < 0 ? 'decrease' : 'stable');
        }
        
        // Tính tổng tiêu thụ năm
        $yearlyConsumption = 0;
        $monthsWithData = 0;
        for ($month = 1; $month <= 12; $month++) {
            $monthlyValue = $this->calculateMonthlyConsumption($meterIds, $month, $currentYear);
            if ($monthlyValue > 0) {
                $monthsWithData++;
            }
            $yearlyConsumption += $monthlyValue;
        }
        
        // Tính số tiền tháng hiện tại (sử dụng logic billing thực tế)
        $currentMonthAmount = $this->calculateCurrentMonthAmount($meterIds, $currentMonth, $currentYear);
        
        // Đếm số công tơ
        $totalMeters = count($meterIds);
        
        // Đếm số hộ tiêu thụ (nếu là UNIT)
        $totalConsumers = 0;
        if ($organization->type === 'UNIT') {
            $totalConsumers = $organization->children()->where('type', 'CONSUMER')->count();
        }
        
        // Tạo description cho stat tháng hiện tại
        $currentMonthDescription = '';
        if ($currentMonthConsumption > 0 && $hasComparison) {
            // Có dữ liệu cả 2 tháng, hiển thị % thay đổi
            $currentMonthDescription = abs(round($changePercent, 1)) . '% so với tháng ' . $lastMonth;
        } elseif ($currentMonthConsumption > 0 && !$hasComparison) {
            // Có tháng này nhưng không có tháng trước
            $currentMonthDescription = 'Tháng ' . $lastMonth . ': chưa có dữ liệu';
        } elseif ($currentMonthConsumption == 0 && $lastMonthConsumption > 0) {
            // Không có tháng này nhưng có tháng trước
            $currentMonthDescription = 'Chưa có dữ liệu. Tháng ' . $lastMonth . ': ' . number_format($lastMonthConsumption, 0, ',', '.') . ' kWh';
        } else {
            // Không có cả 2 tháng
            $currentMonthDescription = 'Chưa có dữ liệu ghi nhận';
        }

        $stats = [
            Stat::make('Tiêu thụ tháng này', number_format($currentMonthConsumption, 0, ',', '.') . ' kWh')
                ->description($currentMonthDescription)
                ->descriptionIcon($currentMonthConsumption == 0 ? 'heroicon-m-exclamation-triangle' : ($trend === 'increase' ? 'heroicon-m-arrow-trending-up' : ($trend === 'decrease' ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-information-circle')))
                ->color($currentMonthConsumption == 0 ? 'warning' : ($trend === 'increase' ? 'danger' : ($trend === 'decrease' ? 'success' : 'gray')))
                ->chart($this->getSparklineData($meterIds, $currentYear, $currentMonth)),
                
            Stat::make('Tổng tiêu thụ năm ' . $currentYear, number_format($yearlyConsumption, 0, ',', '.') . ' kWh')
                ->description($yearlyConsumption > 0 ? 'Trung bình: ' . number_format($monthsWithData > 0 ? $yearlyConsumption / $monthsWithData : 0, 0, ',', '.') . ' kWh/tháng' : 'Chưa có dữ liệu')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
                
            Stat::make('Số tiền tháng này', number_format($currentMonthAmount['total'], 0, ',', '.') . ' đ')
                ->description($currentMonthConsumption > 0 ? 'Đơn giá TB: ' . number_format($currentMonthAmount['avg_price'], 0, ',', '.') . ' đ/kWh' : 'Chưa có dữ liệu tiêu thụ')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
        
        // Thêm stat về số công tơ/hộ tiêu thụ
        if ($organization->type === 'UNIT' && $totalConsumers > 0) {
            $stats[] = Stat::make('Số hộ tiêu thụ', $totalConsumers)
                ->description($totalMeters . ' công tơ')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('info');
        } else {
            $stats[] = Stat::make('Số công tơ', $totalMeters)
                ->description($organization->type === 'UNIT' ? 'Đơn vị tổ chức' : 'Hộ tiêu thụ')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('info');
        }

        return $stats;
    }

    private function getMeterIds(OrganizationUnit $organization): array
    {
        $meterIds = [];
        
        if ($organization->type === 'UNIT') {
            $consumers = $organization->children()->where('type', 'CONSUMER')->get();
            foreach ($consumers as $consumer) {
                $meterIds = array_merge($meterIds, $consumer->electricMeters()->pluck('id')->toArray());
            }
        } else {
            $meterIds = $organization->electricMeters()->pluck('id')->toArray();
        }
        
        return $meterIds;
    }

    private function calculateMonthlyConsumption(array $meterIds, int $month, int $year): float
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $totalConsumption = 0;
        
        foreach ($meterIds as $meterId) {
            // Lấy reading trong tháng này
            $current = MeterReading::where('electric_meter_id', $meterId)
                ->whereBetween('reading_date', [$startDate, $endDate])
                ->orderBy('reading_date', 'desc')
                ->first();
            
            if ($current) {
                // Có reading trong tháng này
                $previous = MeterReading::where('electric_meter_id', $meterId)
                    ->where('reading_date', '<', $current->reading_date)
                    ->orderBy('reading_date', 'desc')
                    ->first();
                
                $meter = \App\Models\ElectricMeter::find($meterId);
                
                if ($previous) {
                    $consumption = ($current->reading_value - $previous->reading_value) * ($meter->hsn ?? 1);
                } else {
                    // Không có reading trước -> coi như bắt đầu từ 0
                    $consumption = $current->reading_value * ($meter->hsn ?? 1);
                }
                
                $totalConsumption += $consumption;
            }
            // Không có reading trong tháng này -> consumption = 0 (không cộng gì cả)
        }
        
        return round($totalConsumption, 2);
    }

    private function calculateCurrentMonthConsumption(array $meterIds, int $month, int $year): float
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $totalConsumption = 0;
        
        foreach ($meterIds as $meterId) {
            // Lấy reading gần nhất đến cuối tháng này
            $current = MeterReading::where('electric_meter_id', $meterId)
                ->where('reading_date', '<=', $endDate)
                ->orderBy('reading_date', 'desc')
                ->first();
            
            if ($current) {
                $previous = MeterReading::where('electric_meter_id', $meterId)
                    ->where('reading_date', '<', $current->reading_date)
                    ->orderBy('reading_date', 'desc')
                    ->first();
                
                $meter = \App\Models\ElectricMeter::find($meterId);
                
                if ($previous) {
                    $consumption = ($current->reading_value - $previous->reading_value) * ($meter->hsn ?? 1);
                } else {
                    // Không có reading trước -> coi như bắt đầu từ 0
                    $consumption = $current->reading_value * ($meter->hsn ?? 1);
                }
                
                $totalConsumption += $consumption;
            }
        }
        
        return round($totalConsumption, 2);
    }

    private function getSparklineData(array $meterIds, int $year, int $currentMonth): array
    {
        $data = [];
        
        // Lấy 6 tháng gần nhất (lùi từ tháng hiện tại)
        for ($i = 5; $i >= 0; $i--) {
            $month = $currentMonth - $i;
            $y = $year;
            
            if ($month < 1) {
                $month += 12;
                $y--;
            }
            
            $data[] = $this->calculateMonthlyConsumption($meterIds, $month, $y);
        }
        
        return $data;
    }

    private function calculateCurrentMonthAmount(array $meterIds, int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $totalAmount = 0;
        $totalChargeableKwh = 0;
        
        foreach ($meterIds as $meterId) {
            // Lấy reading gần nhất đến cuối tháng này
            $current = MeterReading::where('electric_meter_id', $meterId)
                ->where('reading_date', '<=', $endDate)
                ->orderBy('reading_date', 'desc')
                ->first();
            
            if ($current) {
                $previous = MeterReading::where('electric_meter_id', $meterId)
                    ->where('reading_date', '<', $current->reading_date)
                    ->orderBy('reading_date', 'desc')
                    ->first();
                
                $meter = \App\Models\ElectricMeter::find($meterId);
                
                $rawConsumption = 0;
                if ($previous) {
                    $rawConsumption = ($current->reading_value - $previous->reading_value) * ($meter->hsn ?? 1);
                } else {
                    // Không có reading trước -> coi như bắt đầu từ 0
                    $rawConsumption = $current->reading_value * ($meter->hsn ?? 1);
                }
                
                if ($rawConsumption > 0) {
                    // Áp dụng trợ cấp
                    $subsidizedApplied = min($rawConsumption, $meter->subsidized_kwh ?? 0);
                    $chargeableKwh = max(0, $rawConsumption - $subsidizedApplied);
                    
                    // Lấy biểu giá hiện tại
                    $tariff = \App\Models\ElectricityTariff::where('tariff_type_id', $meter->tariff_type_id)
                        ->where('effective_from', '<=', $endDate)
                        ->where(function($q) use ($endDate) {
                            $q->whereNull('effective_to')->orWhere('effective_to', '>=', $endDate);
                        })
                        ->first();
                    
                    if ($tariff && $chargeableKwh > 0) {
                        $amount = $chargeableKwh * $tariff->price_per_kwh;
                        $totalAmount += $amount;
                        $totalChargeableKwh += $chargeableKwh;
                    }
                }
            }
        }
        
        return [
            'total' => $totalAmount,
            'avg_price' => $totalChargeableKwh > 0 ? $totalAmount / $totalChargeableKwh : 0
        ];
    }
}
