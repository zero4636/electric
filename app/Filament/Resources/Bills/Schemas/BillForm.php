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
                Section::make('Bill')
                    ->columns(2)
                    ->components([
                        Select::make('organization_unit_id')
                            ->label('Organization Unit')
                            ->relationship('organizationUnit','name')
                            ->required(),

                        DatePicker::make('billing_date')
                            ->label('Billing Date')
                            ->required(),

                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->disabled(),

                        Select::make('status')
                            ->label('Status')
                            ->options(['PENDING'=>'Pending','PAID'=>'Paid','CANCELLED'=>'Cancelled'])
                            ->default('PENDING'),
                    ]),
            ]);
    }
}
