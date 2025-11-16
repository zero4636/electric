<?php

namespace App\Filament\Resources\Bills\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hóa đơn')
                    ->columns(2)
                    ->components([
                        Select::make('organization_unit_id')
                            ->label('Đơn vị')
                            ->relationship('organizationUnit','name')
                            ->required(),

                        DatePicker::make('billing_month')
                            ->label('Tháng lập hóa đơn')
                            ->displayFormat('m/Y')
                            ->format('Y-m-01')
                            ->required(),

                        DatePicker::make('due_date')
                            ->label('Hạn thanh toán')
                            ->required(),

                        TextInput::make('total_amount')
                            ->label('Tổng tiền')
                            ->numeric()
                            ->disabled(),

                        Select::make('payment_status')
                            ->label('Trạng thái')
                            ->options([
                                'UNPAID' => 'Chưa thanh toán',
                                'PARTIAL' => 'Thanh toán một phần',
                                'PAID' => 'Đã thanh toán',
                                'OVERDUE' => 'Quá hạn',
                            ])
                            ->default('UNPAID')
                            ->required(),
                    ]),
            ]);
    }
}
