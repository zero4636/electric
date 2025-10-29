<?php

namespace App\Filament\Resources\Bills\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use App\Filament\Resources\BillDetails\BillDetailResource;

class BillDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'billDetails';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('electricMeter.meter_number')->label('Công tơ'),
                TextColumn::make('consumption')->label('Tiêu thụ')->suffix(' kWh'),
                TextColumn::make('price_per_kwh')->label('Đơn giá')->money('VND', true),
                TextColumn::make('hsn')->label('Số sê-ri'),
                TextColumn::make('amount')->label('Thành tiền')->money('VND', true),
            ])
            ->filters([
                // you can add filters here if needed
            ])
            ->headerActions([
                CreateAction::make()->label('Tạo mới'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Xem')
                    ->url(fn ($record) => BillDetailResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
                EditAction::make()->label('Sửa'),
                DeleteAction::make()->label('Xóa'),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('electric_meter_id')
                ->label('Công tơ')
                ->relationship('electricMeter','meter_number')
                ->required(),

            TextInput::make('consumption')->label('Tiêu thụ (kWh)')->numeric()->required(),
            TextInput::make('price_per_kwh')->label('Đơn giá')->numeric()->required(),
            TextInput::make('hsn')->label('Số sê-ri')->numeric()->required(),
            TextInput::make('amount')->label('Thành tiền')->numeric()->required(),
        ]);
    }
}
