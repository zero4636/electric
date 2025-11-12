<?php

namespace App\Filament\Resources\ElectricityTariffs\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ElectricityTariffInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin biểu giá')
                    ->columns(2)
                    ->components([
                        TextEntry::make('tariffType.name')
                            ->label('Loại hình tiêu thụ')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('level')
                            ->label('Bậc thang')
                            ->formatStateUsing(fn (int $state): string => "Bậc $state"),
                    ]),

                Section::make('Ngưỡng tiêu thụ')
                    ->columns(2)
                    ->components([
                        TextEntry::make('min_kwh')
                            ->label('Từ (kWh)')
                            ->numeric(0)
                            ->suffix(' kWh'),
                        TextEntry::make('max_kwh')
                            ->label('Đến (kWh)')
                            ->numeric(0)
                            ->suffix(' kWh')
                            ->placeholder('Không giới hạn')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0) . ' kWh' : 'Không giới hạn'),
                    ]),

                Section::make('Giá điện')
                    ->columns(1)
                    ->components([
                        TextEntry::make('price')
                            ->label('Đơn giá')
                            ->money('VND')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success')
                            ->suffix(' đ/kWh'),
                    ]),

                Section::make('Thời gian áp dụng')
                    ->columns(2)
                    ->components([
                        TextEntry::make('effective_date')
                            ->label('Từ ngày')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),
                        TextEntry::make('expiry_date')
                            ->label('Đến ngày')
                            ->date('d/m/Y')
                            ->placeholder('Vô thời hạn')
                            ->icon('heroicon-o-calendar'),
                    ]),
            ]);
    }
}
