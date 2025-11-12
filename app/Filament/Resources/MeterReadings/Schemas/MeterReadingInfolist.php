<?php

namespace App\Filament\Resources\MeterReadings\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class MeterReadingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin công tơ')
                    ->columns(3)
                    ->components([
                        TextEntry::make('electricMeter.meter_number')
                            ->label('Mã công tơ')
                            ->copyable()
                            ->weight('bold')
                            ->color('primary')
                            ->url(fn ($record) => $record->electricMeter 
                                ? route('filament.admin.resources.electric-meters.view', ['record' => $record->electric_meter_id])
                                : null)
                            ->openUrlInNewTab(false),
                        TextEntry::make('electricMeter.organizationUnit.name')
                            ->label('Hộ tiêu thụ')
                            ->limit(50),
                        TextEntry::make('electricMeter.substation.name')
                            ->label('Trạm biến áp')
                            ->placeholder('—'),
                    ]),

                Section::make('Thông tin ghi chỉ số')
                    ->columns(2)
                    ->components([
                        TextEntry::make('reading_date')
                            ->label('Ngày ghi')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),
                        TextEntry::make('reader_name')
                            ->label('Người ghi')
                            ->placeholder('—')
                            ->icon('heroicon-o-user'),
                    ]),

                Section::make('Tiêu thụ điện')
                    ->description('So sánh với chỉ số trước và sau')
                    ->columns(5)
                    ->components([
                        TextEntry::make('previous_reading')
                            ->label('Chỉ số trước')
                            ->numeric(2)
                            ->suffix(' kWh')
                            ->placeholder('—')
                            ->url(function ($record) {
                                if (!$record->electricMeter) {
                                    return null;
                                }
                                $previous = \App\Models\MeterReading::where('electric_meter_id', $record->electric_meter_id)
                                    ->where(function($q) use ($record) {
                                        $q->where('reading_date', '<', $record->reading_date)
                                          ->orWhere(function($q2) use ($record) {
                                              $q2->where('reading_date', '=', $record->reading_date)
                                                 ->where('id', '<', $record->id);
                                          });
                                    })
                                    ->latest('reading_date')
                                    ->latest('id')
                                    ->first();
                                return $previous 
                                    ? route('filament.admin.resources.meter-readings.view', ['record' => $previous->id])
                                    : null;
                            })
                            ->color('info')
                            ->getStateUsing(function ($record) {
                                if (!$record->electricMeter) {
                                    return null;
                                }
                                $previous = \App\Models\MeterReading::where('electric_meter_id', $record->electric_meter_id)
                                    ->where(function($q) use ($record) {
                                        $q->where('reading_date', '<', $record->reading_date)
                                          ->orWhere(function($q2) use ($record) {
                                              $q2->where('reading_date', '=', $record->reading_date)
                                                 ->where('id', '<', $record->id);
                                          });
                                    })
                                    ->latest('reading_date')
                                    ->latest('id')
                                    ->first();
                                return $previous?->reading_value;
                            }),
                        TextEntry::make('previous_date')
                            ->label('Ngày ghi trước')
                            ->date('d/m/Y')
                            ->placeholder('—')
                            ->color('gray')
                            ->getStateUsing(function ($record) {
                                if (!$record->electricMeter) {
                                    return null;
                                }
                                $previous = \App\Models\MeterReading::where('electric_meter_id', $record->electric_meter_id)
                                    ->where(function($q) use ($record) {
                                        $q->where('reading_date', '<', $record->reading_date)
                                          ->orWhere(function($q2) use ($record) {
                                              $q2->where('reading_date', '=', $record->reading_date)
                                                 ->where('id', '<', $record->id);
                                          });
                                    })
                                    ->latest('reading_date')
                                    ->latest('id')
                                    ->first();
                                return $previous?->reading_date;
                            }),
                        TextEntry::make('reading_value')
                            ->label('Chỉ số hiện tại')
                            ->numeric(2)
                            ->suffix(' kWh')
                            ->weight('bold')
                            ->color('primary')
                            ->size('lg'),
                        TextEntry::make('next_reading')
                            ->label('Chỉ số sau')
                            ->numeric(2)
                            ->suffix(' kWh')
                            ->placeholder('—')
                            ->url(function ($record) {
                                if (!$record->electricMeter) {
                                    return null;
                                }
                                $next = \App\Models\MeterReading::where('electric_meter_id', $record->electric_meter_id)
                                    ->where(function($q) use ($record) {
                                        $q->where('reading_date', '>', $record->reading_date)
                                          ->orWhere(function($q2) use ($record) {
                                              $q2->where('reading_date', '=', $record->reading_date)
                                                 ->where('id', '>', $record->id);
                                          });
                                    })
                                    ->oldest('reading_date')
                                    ->oldest('id')
                                    ->first();
                                return $next 
                                    ? route('filament.admin.resources.meter-readings.view', ['record' => $next->id])
                                    : null;
                            })
                            ->color('info')
                            ->getStateUsing(function ($record) {
                                if (!$record->electricMeter) {
                                    return null;
                                }
                                $next = \App\Models\MeterReading::where('electric_meter_id', $record->electric_meter_id)
                                    ->where(function($q) use ($record) {
                                        $q->where('reading_date', '>', $record->reading_date)
                                          ->orWhere(function($q2) use ($record) {
                                              $q2->where('reading_date', '=', $record->reading_date)
                                                 ->where('id', '>', $record->id);
                                          });
                                    })
                                    ->oldest('reading_date')
                                    ->oldest('id')
                                    ->first();
                                return $next?->reading_value;
                            }),
                        TextEntry::make('next_date')
                            ->label('Ngày ghi sau')
                            ->date('d/m/Y')
                            ->placeholder('—')
                            ->color('gray')
                            ->getStateUsing(function ($record) {
                                if (!$record->electricMeter) {
                                    return null;
                                }
                                $next = \App\Models\MeterReading::where('electric_meter_id', $record->electric_meter_id)
                                    ->where(function($q) use ($record) {
                                        $q->where('reading_date', '>', $record->reading_date)
                                          ->orWhere(function($q2) use ($record) {
                                              $q2->where('reading_date', '=', $record->reading_date)
                                                 ->where('id', '>', $record->id);
                                          });
                                    })
                                    ->oldest('reading_date')
                                    ->oldest('id')
                                    ->first();
                                return $next?->reading_date;
                            }),
                    ]),

                Section::make('Tiêu thụ')
                    ->columns(1)
                    ->components([
                        TextEntry::make('consumption')
                            ->label('Tiêu thụ điện (so với lần trước)')
                            ->badge()
                            ->size('lg')
                            ->color('success')
                            ->placeholder('Chưa có chỉ số trước')
                            ->getStateUsing(function ($record) {
                                if (!$record->electricMeter) {
                                    return null;
                                }
                                $previous = \App\Models\MeterReading::where('electric_meter_id', $record->electric_meter_id)
                                    ->where(function($q) use ($record) {
                                        $q->where('reading_date', '<', $record->reading_date)
                                          ->orWhere(function($q2) use ($record) {
                                              $q2->where('reading_date', '=', $record->reading_date)
                                                 ->where('id', '<', $record->id);
                                          });
                                    })
                                    ->latest('reading_date')
                                    ->latest('id')
                                    ->first();
                                
                                if (!$previous) {
                                    return null;
                                }
                                
                                $consumption = ($record->reading_value - $previous->reading_value) * $record->electricMeter->hsn;
                                return number_format($consumption, 2) . ' kWh';
                            }),
                    ]),

                Section::make('Ghi chú')
                    ->columns(1)
                    ->components([
                        TextEntry::make('notes')
                            ->label('Ghi chú')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->hidden(fn ($record) => empty($record->notes)),
            ]);
    }
}
