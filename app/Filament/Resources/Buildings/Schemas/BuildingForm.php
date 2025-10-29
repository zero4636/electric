<?php

namespace App\Filament\Resources\Buildings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BuildingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin tòa nhà')
                    ->description('Nhập thông tin tòa nhà')
                    ->icon('heroicon-o-building-office-2')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Mã tòa nhà')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('Ví dụ: D5, A17, B1')
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('Tên tòa nhà')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Tên đầy đủ của tòa nhà')
                            ->columnSpan(1),

                        Select::make('substation_id')
                            ->label('Trạm biến áp')
                            ->relationship('substation', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Chọn trạm biến áp cung cấp điện')
                            ->columnSpan(1),

                        TextInput::make('total_floors')
                            ->label('Tổng số tầng')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Ví dụ: 10')
                            ->columnSpan(1),

                        Textarea::make('address')
                            ->label('Địa chỉ')
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder('Địa chỉ chi tiết của tòa nhà')
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'ACTIVE' => 'Hoạt động',
                                'INACTIVE' => 'Ngừng hoạt động',
                            ])
                            ->default('ACTIVE')
                            ->native(false)
                            ->columnSpan(1),
                    ]),
            ]);
    }
}

