<?php

namespace App\Filament\Resources\ElectricMeters\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Actions\Action;
use App\Models\ElectricityTariff;

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
                            ->label('Mã công tơ')
                            ->copyable(),
                        TextEntry::make('organizationUnit.name')
                            ->label('Đơn vị'),
                        TextEntry::make('substation.name')
                            ->label('Trạm / Khu vực')
                            ->placeholder('—'),
                    ]),

                Section::make('Vị trí lắp đặt')
                    ->columns(3)
                    ->components([
                        TextEntry::make('building')
                            ->label('Tòa nhà')
                            ->placeholder('—'),
                        TextEntry::make('floor')
                            ->label('Tầng')
                            ->placeholder('—'),
                        TextEntry::make('installation_location')
                            ->label('Vị trí chi tiết')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Thông số kỹ thuật')
                    ->columns(3)
                    ->components([
                        TextEntry::make('phase_type')
                            ->label('Loại pha')
                            ->badge()
                            ->colors([
                                'success' => '1_PHASE',
                                'warning' => '3_PHASE',
                            ])
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                '1_PHASE' => '1 pha',
                                '3_PHASE' => '3 pha',
                                default => '—',
                            })
                            ->placeholder('—'),
                        TextEntry::make('hsn')
                            ->label('HSN (Hệ số nhân)')
                            ->numeric(2),
                        TextEntry::make('subsidized_kwh')
                            ->label('Điện bao cấp (kWh/tháng)')
                            ->numeric(0)
                            ->suffix(' kWh')
                            ->placeholder('0'),
                    ]),

                Section::make('Biểu giá')
                    ->columns(3)
                    ->components([
                        TextEntry::make('tariffType.name')
                            ->label('Loại hình tiêu thụ')
                            ->formatStateUsing(function ($state, $record) {
                                $label = e($state ?? '—');
                                $color = $record->tariffType?->color ?? '#9CA3AF';
                                $hex = ltrim($color, '#');
                                if (strlen($hex) === 3) {
                                    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                                }
                                $r = hexdec(substr($hex, 0, 2));
                                $g = hexdec(substr($hex, 2, 2));
                                $b = hexdec(substr($hex, 4, 2));
                                $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                                $text = $yiq >= 128 ? '#111827' : '#ffffff';
                                return "<span class=\"fi-badge fi-size-sm\" style=\"background-color:#{$hex}; color: {$text};\" title=\"#{$hex}\">{$label}</span>";
                            })
                            ->html()
                            ->placeholder('—'),

                        TextEntry::make('active_tariff_price')
                            ->label('Giá hiện hành')
                            ->getStateUsing(function ($record) {
                                $tariff = ElectricityTariff::getActiveTariff($record->tariff_type_id, now());
                                if (!$tariff) {
                                    return '—';
                                }
                                return number_format((float) $tariff->price_per_kwh, 0, ',', '.') . ' ₫/kWh';
                            })
                            ->weight('bold')
                            ->color('primary')
                            ->placeholder('—'),

                        TextEntry::make('active_tariff_period')
                            ->label('Hiệu lực')
                            ->getStateUsing(function ($record) {
                                $tariff = ElectricityTariff::getActiveTariff($record->tariff_type_id, now());
                                if (!$tariff) {
                                    return '—';
                                }
                                $from = optional($tariff->effective_from)->format('d/m/Y');
                                $to = $tariff->effective_to ? optional($tariff->effective_to)->format('d/m/Y') : 'Không giới hạn';
                                return "Từ {$from} đến {$to}";
                            })
                            ->placeholder('—'),
                    ]),

                Section::make('Trạng thái')
                    ->columns(1)
                    ->components([
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

                Section::make('Tổng quan ghi chỉ số')
                    ->description('Tóm tắt – bảng phân trang đầy đủ ở tab Quan hệ phía dưới')
                    ->columns(4)
                    ->components([
                        TextEntry::make('total_readings')
                            ->label('Tổng số lần ghi')
                            ->badge()
                            ->color('info')
                            ->getStateUsing(fn ($record) => $record->meterReadings()->count()),
                        TextEntry::make('latest_reading_value')
                            ->label('Chỉ số mới nhất')
                            ->suffix(' kWh')
                            ->getStateUsing(fn ($record) => optional($record->meterReadings()->latest('reading_date')->latest('id')->first())->reading_value ?? '—')
                            ->weight('bold')
                            ->color('primary'),
                        TextEntry::make('latest_reading_date')
                            ->label('Ngày ghi mới nhất')
                            ->date('d/m/Y')
                            ->getStateUsing(fn ($record) => optional($record->meterReadings()->latest('reading_date')->latest('id')->first())->reading_date)
                            ->placeholder('—'),
                        TextEntry::make('avg_consumption')
                            ->label('Tiêu thụ bình quân')
                            ->suffix(' kWh')
                            ->getStateUsing(function ($record) {
                                $readings = $record->meterReadings()->orderBy('reading_date')->orderBy('id')->get(['reading_value','reading_date','id']);
                                if ($readings->count() < 2) {
                                    return '—';
                                }
                                $hsn = $record->hsn ?? 1;
                                $total = 0; $segments = 0; $prev = null;
                                foreach ($readings as $r) {
                                    if ($prev) {
                                        $total += ($r->reading_value - $prev->reading_value) * $hsn;
                                        $segments++;
                                    }
                                    $prev = $r;
                                }
                                if ($segments === 0) { return '—'; }
                                return number_format($total / $segments, 2);
                            })
                            ->badge()
                            ->color('success'),
                    ]),
            ]);
    }
}
