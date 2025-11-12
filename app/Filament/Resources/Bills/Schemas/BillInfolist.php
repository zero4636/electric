<?php

namespace App\Filament\Resources\Bills\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class BillInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin hóa đơn')
                    ->columns(3)
                    ->components([
                        TextEntry::make('bill_number')
                            ->label('Số hóa đơn')
                            ->copyable()
                            ->weight('bold')
                            ->color('primary'),
                        TextEntry::make('organizationUnit.name')
                            ->label('Đơn vị')
                            ->limit(50),
                        TextEntry::make('billing_month')
                            ->label('Tháng thanh toán')
                            ->date('m/Y'),
                    ]),

                Section::make('Thông tin thanh toán')
                    ->columns(3)
                    ->components([
                        TextEntry::make('total_amount')
                            ->label('Tổng tiền')
                            ->money('VND')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success'),
                        TextEntry::make('payment_status')
                            ->label('Trạng thái')
                            ->badge()
                            ->colors([
                                'warning' => 'PENDING',
                                'success' => 'PAID',
                                'danger' => 'OVERDUE',
                            ])
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'PENDING' => 'Chưa thanh toán',
                                'PAID' => 'Đã thanh toán',
                                'OVERDUE' => 'Quá hạn',
                                default => $state,
                            }),
                        TextEntry::make('due_date')
                            ->label('Hạn thanh toán')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),
                    ]),

                Section::make('Chi tiết tiêu thụ')
                    ->description(fn ($record) => 'Tổng số: ' . $record->billDetails()->count() . ' công tơ')
                    ->icon('heroicon-o-bolt')
                    ->columns(1)
                    ->components([
                        RepeatableEntry::make('billDetails')
                            ->label('')
                            ->schema([
                                TextEntry::make('electricMeter.meter_number')
                                    ->label('Mã công tơ')
                                    ->weight('bold')
                                    ->copyable(),
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
                                    ->color('info')
                                    ->getStateUsing(fn ($record) => $record->new_reading - $record->old_reading),
                                TextEntry::make('amount')
                                    ->label('Thành tiền')
                                    ->money('VND')
                                    ->weight('bold')
                                    ->color('success'),
                            ])
                            ->columns(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
