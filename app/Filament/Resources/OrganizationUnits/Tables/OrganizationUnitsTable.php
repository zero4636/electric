<?php

namespace App\Filament\Resources\OrganizationUnits\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
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
                    ->formatStateUsing(fn($state, $record) => str_repeat('— ', $record->depth) . $state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')->label('Mã')->sortable(),
                TextColumn::make('parent.name')
                    ->label('Đơn vị cấp trên')
                    ->sortable(query: function ($query, $direction) {
                        return $query
                            ->leftJoin('organization_units as parent_units', 'organization_units.parent_id', '=', 'parent_units.id')
                            ->orderBy('parent_units.name', $direction)
                            ->select('organization_units.*');
                    }),
                TextColumn::make('breadcrumb')
                    ->label('Chuỗi cấp')
                    ->getStateUsing(fn($record) => $record->breadcrumb)
                    ->wrap()
                    ->toggleable(false),

                TextColumn::make('meters_count')
                    ->label('Số công tơ')
                    ->getStateUsing(fn($record) => $record->electricMeters()->count())
                    ->sortable(),

                TextColumn::make('total_consumption')
                    ->label('Tổng tiêu thụ')
                    ->getStateUsing(function ($record) {
                        // sum consumption from bill_details for this organization
                        $sum = DB::table('bill_details')
                            ->join('bills', 'bill_details.bill_id', '=', 'bills.id')
                            ->where('bills.organization_unit_id', $record->id)
                            ->sum('bill_details.consumption');
                        return $sum ?: 0;
                    })
                    ->suffix(' kWh')
                    ->sortable(),

                TextColumn::make('total_billed')
                    ->label('Tổng tiền')
                    ->getStateUsing(fn($record) => $record->bills()->sum('total_amount'))
                    ->money('VND', true)
                    ->sortable(),

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
                    }),

                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => 'ACTIVE',
                        'danger' => 'INACTIVE',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'Hoạt động',
                        'INACTIVE' => 'Ngừng hoạt động',
                        default => $state,
                    }),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
