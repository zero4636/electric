<?php

namespace App\Filament\Resources\Bills\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
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
                TextColumn::make('billing_month')
                    ->label('Tháng')
                    ->date('m/Y')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('organizationUnit.name')
                    ->label('Đơn vị')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('details_count')
                    ->label('Số công tơ')
                    ->counts('billDetails')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
                TextColumn::make('total_amount')
                    ->label('Tổng tiền')
                    ->money('VND', true)
                    ->sortable()
                    ->weight('bold')
                    ->alignRight(),
                TextColumn::make('payment_status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'UNPAID' => 'warning',
                        'PARTIAL' => 'info',
                        'PAID' => 'success',
                        'OVERDUE' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'UNPAID' => 'Chưa TT',
                        'PARTIAL' => 'TT 1 phần',
                        'PAID' => 'Đã TT',
                        'OVERDUE' => 'Quá hạn',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Hạn TT')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('payment_status')
                    ->form([
                        Select::make('payment_status')
                            ->label('Trạng thái')
                            ->options([
                                'UNPAID' => 'Chưa thanh toán',
                                'PARTIAL' => 'Thanh toán 1 phần',
                                'PAID' => 'Đã thanh toán',
                                'OVERDUE' => 'Quá hạn',
                            ])
                    ])
                    ->query(function ($query, $data) {
                        return $query->when($data['payment_status'] ?? null, fn($q, $s) => $q->where('payment_status', $s));
                    }),

                Filter::make('billing_month')
                    ->form([
                        DatePicker::make('from')->label('Từ tháng')->displayFormat('m/Y'),
                        DatePicker::make('until')->label('Đến tháng')->displayFormat('m/Y'),
                    ])
                    ->query(function ($query, $data) {
                        return $query->when(isset($data['from']), fn($q) => $q->where('billing_month', '>=', $data['from']))
                                     ->when(isset($data['until']), fn($q) => $q->where('billing_month', '<=', $data['until']));
                    }),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tạo Hóa đơn mới'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
