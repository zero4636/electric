<?php

namespace App\Filament\Resources\TariffTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TariffTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin loại biểu giá')
                    ->description('Cấu hình loại biểu giá điện')
                    ->icon('heroicon-o-rectangle-stack')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Mã loại')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('VD: RESIDENTIAL, COMMERCIAL')
                            ->helperText('Mã viết HOA, không dấu, có thể dùng gạch dưới')
                            ->regex('/^[A-Z_]+$/')
                            ->validationMessages([
                                'regex' => 'Mã chỉ được chứa chữ HOA và gạch dưới',
                            ]),

                        TextInput::make('name')
                            ->label('Tên loại')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('VD: Dân cư, Thương mại'),

                        ColorPicker::make('color')
                            ->label('Màu sắc')
                            ->required()
                            ->helperText('Chọn mã màu (hex), ví dụ: #0ea5e9'),

                        TextInput::make('icon')
                            ->label('Icon')
                            ->maxLength(50)
                            ->placeholder('VD: heroicon-o-bolt')
                            ->helperText('Tên icon Heroicons (tùy chọn). Tham khảo: https://heroicons.com (dùng tiền tố heroicon-o- hoặc heroicon-s-)'),

                        TextInput::make('sort_order')
                            ->label('Thứ tự')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Số càng nhỏ càng lên đầu'),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'ACTIVE' => 'Hoạt động',
                                'INACTIVE' => 'Ngừng',
                            ])
                            ->default('ACTIVE')
                            ->native(false),

                        Textarea::make('description')
                            ->label('Mô tả')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Mô tả chi tiết về loại biểu giá này'),
                    ]),
            ]);
    }
}

