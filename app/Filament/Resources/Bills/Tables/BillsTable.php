<?php

namespace App\Filament\Resources\Bills\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;

class BillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('organizationUnit.name')->label('Đơn vị')->sortable(),
                TextColumn::make('billing_date')->label('Ngày lập')->date()->sortable(),
                TextColumn::make('total_amount')->label('Tổng tiền')->money('VND', true)->sortable(),
                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors(['warning'=>'PENDING','success'=>'PAID','danger'=>'CANCELLED'])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'PENDING' => 'Chờ thanh toán',
                        'PAID' => 'Đã thanh toán',
                        'CANCELLED' => 'Đã hủy',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'PENDING' => 'Chờ thanh toán',
                                'PAID' => 'Đã thanh toán',
                                'CANCELLED' => 'Đã hủy',
                            ])
                    ])
                    ->query(function ($query, $data) {
                        return $query->when($data['status'] ?? null, fn($q, $s) => $q->where('status', $s));
                    }),

                Filter::make('billing_date')
                    ->form([
                        DatePicker::make('from')->label('Từ ngày'),
                        DatePicker::make('until')->label('Đến ngày'),
                    ])
                    ->query(function ($query, $data) {
                        return $query->when(isset($data['from']), fn($q) => $q->where('billing_date', '>=', $data['from']))
                                     ->when(isset($data['until']), fn($q) => $q->where('billing_date', '<=', $data['until']));
                    }),
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
