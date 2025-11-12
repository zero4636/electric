<?php

namespace App\Filament\Pages\Schemas;

use App\Models\ElectricMeter;
use App\Models\Substation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BulkMeterReadingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Chọn trạm/khu vực')
                        ->schema([
                            Select::make('substation_id')
                                ->label('Trạm biến áp / Khu vực')
                                ->options(Substation::where('status', 'ACTIVE')->pluck('name', 'id'))
                                ->required()
                                ->live()
                                ->searchable()
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        $meters = ElectricMeter::where('substation_id', $state)
                                            ->where('status', 'ACTIVE')
                                            ->with(['organizationUnit', 'meterReadings' => function ($query) {
                                                $query->latest('reading_date')->limit(1);
                                            }])
                                            ->get();

                                        $readings = [];
                                        foreach ($meters as $meter) {
                                            $latestReading = $meter->meterReadings->first();
                                            $readings[] = [
                                                'electric_meter_id' => $meter->id,
                                                'meter_number' => $meter->meter_number,
                                                'organization' => $meter->organizationUnit?->name,
                                                'location' => $meter->installation_location,
                                                'latest_reading' => $latestReading?->reading_value,
                                                'reading_value' => null,
                                                'consumption' => null,
                                            ];
                                        }
                                        $set('readings', $readings);
                                    }
                                }),

                            DatePicker::make('reading_date')
                                ->label('Ngày ghi chỉ số')
                                ->required()
                                ->default(now()),

                            TextInput::make('reader_name')
                                ->label('Người ghi')
                                ->required(),
                        ]),

                    Wizard\Step::make('Nhập chỉ số')
                        ->schema([
                            Repeater::make('readings')
                                ->label('Danh sách công tơ')
                                ->schema([
                                    TextInput::make('meter_number')
                                        ->label('Mã công tơ')
                                        ->disabled(),

                                    TextInput::make('organization')
                                        ->label('Đơn vị')
                                        ->disabled(),

                                    TextInput::make('location')
                                        ->label('Vị trí')
                                        ->disabled(),

                                    TextInput::make('latest_reading')
                                        ->label('Chỉ số cũ')
                                        ->disabled(),

                                    TextInput::make('reading_value')
                                        ->label('Chỉ số mới')
                                        ->numeric()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set, Get $get) {
                                            $latestReading = $get('latest_reading');
                                            if ($state && $latestReading) {
                                                $consumption = $state - $latestReading;
                                                $set('consumption', $consumption > 0 ? $consumption : 0);
                                            }
                                        }),

                                    TextInput::make('consumption')
                                        ->label('Tiêu thụ (kWh)')
                                        ->disabled(),

                                    TextInput::make('electric_meter_id')
                                        ->hidden(),
                                ])
                                ->columns(7)
                                ->reorderable(false)
                                ->addable(false)
                                ->deletable(false)
                                ->defaultItems(0),
                        ]),
                ])
                    ->submitAction(view('filament.pages.bulk-meter-reading-submit')),
            ]);
    }
}
