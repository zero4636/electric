<?php

namespace App\Filament\Resources\OrganizationUnits\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\DB;

class OrganizationUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên đơn vị')
                    ->formatStateUsing(fn($state, $record) => str_repeat('—— ', $record->depth) . $state)
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('code')
                    ->label('Mã')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('parent.name')
                    ->label('Đơn vị cha')
                    ->sortable(query: function ($query, $direction) {
                        return $query
                            ->leftJoin('organization_units as parent_units', 'organization_units.parent_id', '=', 'parent_units.id')
                            ->orderBy('parent_units.name', $direction)
                            ->select('organization_units.*');
                    })
                    ->limit(25)
                    ->placeholder('—'),
                TextColumn::make('meters_count')
                    ->label('Số công tơ')
                    ->getStateUsing(fn($record) => $record->electricMeters()->count())
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                BadgeColumn::make('type')
                    ->label('Loại')
                    ->colors([
                        'primary' => 'ORGANIZATION',
                        'success' => 'UNIT',
                        'warning' => 'CONSUMER',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ORGANIZATION' => 'Tổ chức',
                        'UNIT' => 'Đơn vị',
                        'CONSUMER' => 'Khách hàng',
                        default => $state,
                    })
                    ->sortable(),
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
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'ORGANIZATION' => 'Tổ chức',
                        'UNIT' => 'Đơn vị',
                        'CONSUMER' => 'Khách hàng',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng hoạt động',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tạo Đơn vị tổ chức mới'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
