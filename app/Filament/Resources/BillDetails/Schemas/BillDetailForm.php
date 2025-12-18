<?php

namespace App\Filament\Resources\BillDetails\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Helpers\OrganizationHelper;

class BillDetailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Chi tiết hóa đơn')
                    ->columns(2)
                    ->components([
                        Select::make('bill_id')
                            ->label('Hóa đơn')
                            ->relationship(
                                'bill',
                                'id',
                                fn($query) => $query
                                    ->with('organizationUnit')
                                    ->whereHas('organizationUnit', fn ($q) => 
                                        OrganizationHelper::scopeOrganizationUnitsToUser($q)
                                    )
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "#{$record->id} - {$record->organizationUnit->name} - " . $record->billing_month->format('m/Y'))
                            ->required()
                            ->searchable(),

                        Select::make('electric_meter_id')
                            ->label('Công tơ')
                            ->relationship(
                                'electricMeter',
                                'meter_number',
                                fn ($query) => OrganizationHelper::scopeElectricMetersToUserOrganizations($query)
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                $record->meter_number . ' - ' . $record->organizationUnit?->name ?? ''
                            )
                            ->searchable()
                            ->required(),

                        TextInput::make('consumption')
                            ->label('Tiêu thụ (kWh)')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                        
                        TextInput::make('subsidized_applied')
                            ->label('Bao cấp (kWh)')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),
                        
                        TextInput::make('chargeable_kwh')
                            ->label('Tính tiền (kWh)')
                            ->numeric()
                            ->step(0.01),
                        
                        TextInput::make('price_per_kwh')
                            ->label('Đơn giá (VNĐ/kWh)')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                        
                        TextInput::make('hsn')
                            ->label('Hệ số nhân')
                            ->numeric()
                            ->step(0.01)
                            ->default(1)
                            ->required(),
                        
                        TextInput::make('amount')
                            ->label('Thành tiền (VNĐ)')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                    ]),
            ]);
    }
}
