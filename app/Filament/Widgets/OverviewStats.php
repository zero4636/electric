<?php

namespace App\Filament\Widgets;

use App\Models\ElectricMeter;
use App\Models\OrganizationUnit;
use App\Models\Substation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected function getCards(): array
    {
        $units = OrganizationUnit::where('type', 'UNIT')->count();
        $consumers = OrganizationUnit::where('type', 'CONSUMER')->count();
        $meters = ElectricMeter::count();
        $activeMeters = ElectricMeter::where('status', 'ACTIVE')->count();
        $inactiveMeters = ElectricMeter::where('status', 'INACTIVE')->count();
        $substations = Substation::count();

        return [
            Stat::make('Đơn vị chủ quản', number_format($units))
                ->description('Tổng số UNIT')
                ->icon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('Hộ tiêu thụ', number_format($consumers))
                ->description('Tổng số CONSUMER')
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Công tơ điện', number_format($meters))
                ->description("Hoạt động: {$activeMeters} · Ngừng: {$inactiveMeters}")
                ->icon('heroicon-o-light-bulb')
                ->color('warning'),

            Stat::make('Trạm biến áp', number_format($substations))
                ->description('Số trạm đang quản lý')
                ->icon('heroicon-o-bolt')
                ->color('info'),
        ];
    }
}
