<?php

namespace App\Filament\Resources\OrganizationUnits\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\Action;

class BillsRelationManager extends RelationManager
{
    protected static string $relationship = 'bills';

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $title = 'Hóa đơn';
    
    protected static ?string $modelLabel = 'Hóa đơn';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->prefix('HĐ')
                    ->badge(),
                    
                TextColumn::make('billing_month')
                    ->label('Kỳ hóa đơn')
                    ->date('m/Y')
                    ->sortable(),
                    
                TextColumn::make('due_date')
                    ->label('Ngày đến hạn')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->due_date < now() ? 'danger' : 'gray'),
                    
                TextColumn::make('billDetails')
                    ->label('Tiêu thụ')
                    ->formatStateUsing(function ($record) {
                        $totalConsumption = $record->billDetails->sum('consumption');
                        return $totalConsumption > 0 ? number_format($totalConsumption, 0, ',', '.') . ' kWh' : '-';
                    })
                    ->color('primary'),
                    
                TextColumn::make('total_amount')
                    ->label('Tổng tiền')
                    ->money('VND', true)
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                    
                BadgeColumn::make('payment_status')
                    ->label('Trạng thái')
                    ->colors([
                        'warning' => 'UNPAID',
                        'info' => 'PARTIAL', 
                        'success' => 'PAID',
                        'danger' => 'OVERDUE'
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'UNPAID' => 'Chưa thanh toán',
                        'PARTIAL' => 'Thanh toán một phần',
                        'PAID' => 'Đã thanh toán',
                        'OVERDUE' => 'Quá hạn',
                        default => $state,
                    })
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('billing_month', 'desc')
            ->recordActions([
                EditAction::make()->label('Sửa'),
                DeleteAction::make()->label('Xóa'),
            ])
            ->headerActions([
                CreateAction::make()->label('Tạo mới'),
            ]);
    }
}
