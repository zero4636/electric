<?php

namespace App\Filament\Resources\BillDetails\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class BillDetailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin hóa đơn')
                    ->columns(3)
                    ->components([
                        TextEntry::make('bill.bill_number')
                            ->label('Số hóa đơn')
                            ->weight('bold')
                            ->copyable(),
                        TextEntry::make('bill.billing_month')
                            ->label('Tháng thanh toán')
                            ->date('m/Y'),
                        TextEntry::make('bill.organizationUnit.name')
                            ->label('Đơn vị')
                            ->limit(50),
                    ]),

                Section::make('Công tơ điện')
                    ->columns(3)
                    ->components([
                        TextEntry::make('electricMeter.meter_number')
                            ->label('Mã công tơ')
                            ->copyable()
                            ->weight('bold'),
                        TextEntry::make('electricMeter.organizationUnit.name')
                            ->label('Hộ tiêu thụ')
                            ->limit(40),
                        TextEntry::make('hsn')
                            ->label('HSN (Hệ số nhân)')
                            ->numeric(2)
                            ->badge()
                            ->color('info'),
                    ]),

                Section::make('Chỉ số ghi nhận')
                    ->columns(4)
                    ->components([
                        TextEntry::make('old_reading')
                            ->label('Chỉ số cũ')
                            ->numeric(2)
                            ->suffix(' kWh'),
                        TextEntry::make('new_reading')
                            ->label('Chỉ số mới')
                            ->numeric(2)
                            ->suffix(' kWh'),
                        TextEntry::make('consumption')
                            ->label('Tiêu thụ')
                            ->numeric(2)
                            ->suffix(' kWh')
                            ->badge()
                            ->color('warning')
                            ->weight('bold')
                            ->getStateUsing(fn ($record) => $record->new_reading - $record->old_reading),
                        TextEntry::make('reading_date')
                            ->label('Ngày ghi')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),
                    ]),

                Section::make('Tính tiền')
                    ->columns(2)
                    ->components([
                        TextEntry::make('price_per_kwh')
                            ->label('Đơn giá')
                            ->money('VND')
                            ->suffix(' đ/kWh'),
                        TextEntry::make('amount')
                            ->label('Thành tiền')
                            ->money('VND')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success'),
                    ]),
            ]);
    }
}
