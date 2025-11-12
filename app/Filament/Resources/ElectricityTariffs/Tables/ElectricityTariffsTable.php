<?php

namespace App\Filament\Resources\ElectricityTariffs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ColorColumn;

class ElectricityTariffsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tariffType.name')
                    ->label('Loại')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
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
                    ->html(),
                TextColumn::make('price_per_kwh')
                    ->label('Giá (VNĐ/kWh)')
                    ->money('VND')
                    ->sortable()
                    ->alignRight()
                    ->weight('bold'),
                TextColumn::make('effective_from')
                    ->label('Từ ngày')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('effective_to')
                    ->label('Đến ngày')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),
                BadgeColumn::make('is_active')
                    ->label('Trạng thái')
                    ->getStateUsing(function ($record) {
                        $today = now();
                        $from = \Carbon\Carbon::parse($record->effective_from);
                        $to = $record->effective_to ? \Carbon\Carbon::parse($record->effective_to) : null;
                        
                        if ($today->lt($from)) {
                            return 'upcoming';
                        }
                        if ($to && $today->gt($to)) {
                            return 'expired';
                        }
                        return 'active';
                    })
                    ->colors([
                        'success' => 'active',
                        'warning' => 'upcoming',
                        'danger' => 'expired',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Đang dùng',
                        'upcoming' => 'Chưa dùng',
                        'expired' => 'Hết hạn',
                        default => $state,
                    }),
            ])
            ->filters([
                //
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tạo Biểu giá điện mới'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
