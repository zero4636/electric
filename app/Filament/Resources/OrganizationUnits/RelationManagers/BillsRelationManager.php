<?php

namespace App\Filament\Resources\OrganizationUnits\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

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
                TextColumn::make('billing_date')->label('Ngày hóa đơn')->date()->sortable(),
                TextColumn::make('total_amount')->label('Tổng tiền')->money('VND', true)->sortable(),
                BadgeColumn::make('status')->label('Trạng thái')
                    ->colors([
                        'warning' => 'PENDING',
                        'success' => 'PAID',
                        'danger' => 'CANCELLED'
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'PENDING' => 'Chờ thanh toán',
                        'PAID' => 'Đã thanh toán',
                        'CANCELLED' => 'Đã hủy',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tạo mới'),
            ])
            ->recordActions([
                EditAction::make()->label('Sửa'),
                DeleteAction::make()->label('Xóa'),
            ]);
    }
}
