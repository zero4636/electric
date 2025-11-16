<?php

namespace App\Filament\Resources\ElectricMeters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ElectricMetersTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('meter_number')
                    ->label('Mã công tơ')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->icon('heroicon-o-bolt'),
                    
                TextColumn::make('organizationUnit.name')
                    ->label('Hộ tiêu thụ điện')
                    ->searchable()
                    ->sortable()
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->organizationUnit?->name)
                    ->wrap(),
                    
                TextColumn::make('substation.name')
                    ->label('Trạm biến áp')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),
                    
                TextColumn::make('organizationUnit.building')
                    ->label('Nhà/Tòa')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                    
                TextColumn::make('organizationUnit.address')
                    ->label('Địa chỉ')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('installation_location')
                    ->label('Vị trí đặt công tơ')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
                    
                BadgeColumn::make('phase_type')
                    ->label('Loại')
                    ->colors([
                        'success' => '1_PHASE',
                        'warning' => '3_PHASE',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        '1_PHASE' => '1 pha',
                        '3_PHASE' => '3 pha',
                        default => '—',
                    })
                    ->sortable(),
                    
                TextColumn::make('tariffType.name')
                    ->label('Loại hình')
                    ->formatStateUsing(function ($state, $record) {
                        $label = e($state ?? '—');
                        $color = $record->tariffType?->color ?? '#9CA3AF'; // gray-400 fallback
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
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('hsn')
                    ->label('HSN')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),
                    
                TextColumn::make('subsidized_kwh')
                    ->label('Bao cấp')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' kWh')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => 'ACTIVE',
                        'danger' => 'INACTIVE',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng',
                        default => $state,
                    })
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('substation_id')
                    ->label('Trạm biến áp')
                    ->relationship('substation', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('phase_type')
                    ->label('Loại công tơ')
                    ->options([
                        '1_PHASE' => '1 pha',
                        '3_PHASE' => '3 pha',
                    ]),
                    
                SelectFilter::make('tariff_type_id')
                    ->label('Loại hình tiêu thụ')
                    ->relationship('tariffType', 'name')
                    ->searchable()
                    ->preload(),
                    
                TernaryFilter::make('status')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Hoạt động')
                    ->falseLabel('Ngừng hoạt động')
                    ->queries(
                        true: fn ($query) => $query->where('status', 'ACTIVE'),
                        false: fn ($query) => $query->where('status', 'INACTIVE'),
                    ),
                    
                SelectFilter::make('building')
                    ->label('Nhà/Tòa nhà')
                    ->options(fn () => \App\Models\OrganizationUnit::query()
                        ->where('type', 'CONSUMER')
                        ->whereNotNull('building')
                        ->distinct()
                        ->pluck('building', 'building')
                        ->toArray()
                    )
                    ->query(function ($query, $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('organizationUnit', function ($q) use ($data) {
                                $q->where('building', $data['value']);
                            });
                        }
                    })
                    ->searchable(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('meter_number', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
