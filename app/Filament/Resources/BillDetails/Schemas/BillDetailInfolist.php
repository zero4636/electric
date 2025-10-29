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
                        TextEntry::make('bill.id')->label('Mã hóa đơn'),
                        TextEntry::make('bill.billing_date')->label('Ngày lập')->dateTime('d/m/Y'),
                        TextEntry::make('bill.organizationUnit.name')->label('Đơn vị'),
                    ]),

                Section::make('Công tơ')
                    ->columns(3)
                    ->components([
                        TextEntry::make('electricMeter.meter_number')->label('Số công tơ'),
                        TextEntry::make('hsn')->label('HSN')->numeric(2),
                        TextEntry::make('electricMeter.organizationUnit.name')->label('Đơn vị quản lý'),
                    ]),

                Section::make('Chỉ số')
                    ->columns(4)
                    ->components([
                        TextEntry::make('startReading.reading_date')->label('Ngày đầu')->date('d/m/Y')->placeholder('—'),
                        TextEntry::make('startReading.reading_value')->label('Chỉ số đầu')->numeric(2)->placeholder('—'),
                        TextEntry::make('endReading.reading_date')->label('Ngày cuối')->date('d/m/Y')->placeholder('—'),
                        TextEntry::make('endReading.reading_value')->label('Chỉ số cuối')->numeric(2)->placeholder('—'),
                    ]),

                Section::make('Tính tiền')
                    ->columns(3)
                    ->components([
                        TextEntry::make('consumption')->label('Tiêu thụ (kWh)')->numeric(2),
                        TextEntry::make('price_per_kwh')->label('Đơn giá (đ/kWh)')->money('VND', true),
                        TextEntry::make('amount')->label('Thành tiền')->money('VND', true),
                        TextEntry::make('subsidized_amount')->label('Hỗ trợ')->money('VND', true)->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
