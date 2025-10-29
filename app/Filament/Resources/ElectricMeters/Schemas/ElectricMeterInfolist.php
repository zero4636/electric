<?php

namespace App\Filament\Resources\ElectricMeters\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ElectricMeterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin công tơ')
                    ->columns(3)
                    ->components([
                        TextEntry::make('meter_number')
                            ->label('Số công tơ')
                            ->copyable(),
                        TextEntry::make('organizationUnit.name')
                            ->label('Đơn vị'),
                        TextEntry::make('building.name')
                            ->label('Tòa nhà')
                            ->placeholder('—'),
                    ]),

                Section::make('Vị trí lắp đặt')
                    ->columns(2)
                    ->components([
                        TextEntry::make('substation.name')
                            ->label('Trạm biến áp')
                            ->placeholder('—'),
                        TextEntry::make('installation_location')
                            ->label('Vị trí lắp đặt')
                            ->placeholder('—'),
                    ]),

                Section::make('Thông số')
                    ->columns(3)
                    ->components([
                        TextEntry::make('hsn')
                            ->label('HSN (Hệ số nhân)')
                            ->numeric(2),
                        TextEntry::make('meter_book')
                            ->label('Sổ công tơ')
                            ->placeholder('—'),
                        TextEntry::make('status')
                            ->label('Trạng thái')
                            ->badge()
                            ->colors([
                                'success' => 'ACTIVE',
                                'danger' => 'INACTIVE',
                            ])
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'ACTIVE' => 'Hoạt động',
                                'INACTIVE' => 'Ngừng',
                                default => $state,
                            }),
                    ]),

                Section::make('Chỉ số mới nhất')
                    ->columns(3)
                    ->components([
                        TextEntry::make('latestReading.reading_date')
                            ->label('Ngày ghi')
                            ->date('d/m/Y')
                            ->placeholder('—')
                            ->getStateUsing(function ($record) {
                                return $record->meterReadings()
                                    ->latest('reading_date')
                                    ->first()?->reading_date;
                            }),
                        TextEntry::make('latestReading.reading_value')
                            ->label('Chỉ số')
                            ->numeric(2)
                            ->placeholder('—')
                            ->getStateUsing(function ($record) {
                                return $record->meterReadings()
                                    ->latest('reading_date')
                                    ->first()?->reading_value;
                            }),
                        TextEntry::make('latestReading.reader_name')
                            ->label('Người ghi')
                            ->placeholder('—')
                            ->getStateUsing(function ($record) {
                                return $record->meterReadings()
                                    ->latest('reading_date')
                                    ->first()?->reader_name;
                            }),
                    ]),
            ]);
    }
}
