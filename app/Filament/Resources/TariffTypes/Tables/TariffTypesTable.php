<?php

namespace App\Filament\Resources\TariffTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Filters\SelectFilter;

class TariffTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Mã')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                TextColumn::make('name')
                    ->label('Tên loại')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        $label = e($state ?? '—');
                        $color = $record->color ?? '#9CA3AF';
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
                    
                TextColumn::make('description')
                    ->label('Mô tả')
                    ->limit(50)
                    ->wrap()
                    ->placeholder('—'),
                    
                TextColumn::make('tariffs_count')
                    ->label('Số biểu giá')
                    ->getStateUsing(fn($record) => $record->electricityTariffs()->count())
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                    
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->sortable()
                    ->alignCenter(),
                    
                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => 'ACTIVE',
                        'danger' => 'INACTIVE',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ACTIVE' => '✓',
                        'INACTIVE' => '✗',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng',
                    ]),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tạo loại biểu giá mới'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

