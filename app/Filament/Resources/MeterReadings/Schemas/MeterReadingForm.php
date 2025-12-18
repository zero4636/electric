<?php

namespace App\Filament\Resources\MeterReadings\Schemas;

use App\Models\MeterReading;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use App\Helpers\OrganizationHelper;

class MeterReadingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Thông tin công tơ')
                    ->description('Chọn công tơ cần ghi chỉ số')
                    ->icon('heroicon-o-bolt')
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        Select::make('electric_meter_id')
                            ->label('Công tơ điện')
                            ->relationship(
                                'electricMeter',
                                'meter_number',
                                fn ($query) => OrganizationHelper::scopeElectricMetersToUserOrganizations($query)
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                $record->meter_number . ' - ' . $record->organizationUnit?->name ?? ''
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->live()
                            ->default(function () {
                                // Auto-fill từ URL parameter khi tạo mới
                                return request()->query('electric_meter_id');
                            })
                            ->afterStateHydrated(function ($state, $set, $record) {
                                // Load dữ liệu khi edit existing record HOẶC khi có default value
                                if ($state) {
                                    $meter = \App\Models\ElectricMeter::find($state);
                                    if ($meter) {
                                        $latestReading = $meter->meterReadings()
                                            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                            ->latest('reading_date')
                                            ->first();
                                        
                                        $set('_latest_reading', $latestReading?->reading_value);
                                        $set('_latest_date', $latestReading?->reading_date?->format('d/m/Y'));
                                        $set('_organization', $meter->organizationUnit?->name);
                                        $set('_substation', $meter->substation?->name);
                                        $set('_location', $meter->installation_location);
                                    }
                                }
                            })
                            ->afterStateUpdated(function ($state, $set, $record) {
                                if ($state) {
                                    $meter = \App\Models\ElectricMeter::find($state);
                                    if ($meter) {
                                        $latestReading = $meter->meterReadings()
                                            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                            ->latest('reading_date')
                                            ->first();
                                        
                                        $set('_latest_reading', $latestReading?->reading_value);
                                        $set('_latest_date', $latestReading?->reading_date?->format('d/m/Y'));
                                        $set('_organization', $meter->organizationUnit?->name);
                                        $set('_substation', $meter->substation?->name);
                                        $set('_location', $meter->installation_location);
                                    }
                                }
                            })
                            ->helperText('Tìm kiếm theo số công tơ')
                            ->columnSpan(2),

                        DatePicker::make('reading_date')
                            ->label('Ngày ghi')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection()
                            ->default(now())
                            ->maxDate(now())
                            ->helperText('Ngày ghi chỉ số')
                            ->columnSpan(1),

                        TextInput::make('reader_name')
                            ->label('Người ghi')
                            ->maxLength(255)
                            ->placeholder('Tên người ghi chỉ số')
                            ->default(auth()->user()?->name)
                            ->columnSpan(1),

                        TextInput::make('reading_value')
                            ->label('Chỉ số mới (kWh)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->suffix('kWh')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, Get $get) {
                                $latestReading = $get('_latest_reading');
                                if ($state && $latestReading) {
                                    $consumption = $state - $latestReading;
                                    $set('_consumption', $consumption);
                                }
                            })
                            ->placeholder('Ví dụ: 1234.56')
                            ->helperText('Nhập chỉ số hiện tại trên công tơ')
                            ->rules([
                                function (Get $get, $record) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                        $meterId = $get('electric_meter_id');
                                        if (!$meterId) {
                                            return;
                                        }

                                        $latestReading = MeterReading::where('electric_meter_id', $meterId)
                                            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                            ->latest('reading_date')
                                            ->first();

                                        if ($latestReading && $value <= $latestReading->reading_value) {
                                            $fail("Chỉ số mới ({$value}) phải lớn hơn chỉ số cũ ({$latestReading->reading_value})");
                                        }
                                    };
                                },
                            ])
                            ->columnSpan(1),

                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(2)
                            ->columnSpanFull()
                            ->maxLength(1000)
                            ->placeholder('Ghi chú về lần đọc này (nếu có)'),
                        
                        // Hidden fields to store meter info
                        TextInput::make('_latest_reading')->hidden()->dehydrated(false),
                        TextInput::make('_latest_date')->hidden()->dehydrated(false),
                        TextInput::make('_organization')->hidden()->dehydrated(false),
                        TextInput::make('_substation')->hidden()->dehydrated(false),
                        TextInput::make('_location')->hidden()->dehydrated(false),
                        TextInput::make('_consumption')->hidden()->dehydrated(false),
                    ]),

                Section::make('Thông tin tham khảo')
                    ->description('Dữ liệu từ công tơ đã chọn')
                    ->icon('heroicon-o-information-circle')
                    ->columnSpan(1)
                    ->schema([
                        Placeholder::make('org_info')
                            ->label('Đơn vị')
                            ->content(fn (Get $get): string => $get('_organization') ?: '—'),

                        Placeholder::make('substation_info')
                            ->label('Trạm/Khu vực')
                            ->content(fn (Get $get): string => $get('_substation') ?: '—'),

                        Placeholder::make('location_info')
                            ->label('Vị trí')
                            ->content(fn (Get $get): string => $get('_location') ?: '—'),

                        Placeholder::make('latest_reading_info')
                            ->label('Chỉ số cũ')
                            ->content(function (Get $get): string {
                                $value = $get('_latest_reading');
                                $date = $get('_latest_date');
                                if ($value) {
                                    return number_format($value, 2) . ' kWh' . ($date ? " ({$date})" : '');
                                }
                                return '—';
                            }),

                        Placeholder::make('consumption_info')
                            ->label('Tiêu thụ dự kiến')
                            ->content(function (Get $get): string {
                                $consumption = $get('_consumption');
                                if ($consumption !== null && $consumption !== '') {
                                    $color = $consumption > 0 ? 'text-success-600' : 'text-danger-600';
                                    return '<span class="font-bold ' . $color . '">' . 
                                           number_format($consumption, 2) . ' kWh</span>';
                                }
                                return '—';
                            })
                            ->extraAttributes(['class' => 'text-lg']),
                    ]),
            ]);
    }
}
