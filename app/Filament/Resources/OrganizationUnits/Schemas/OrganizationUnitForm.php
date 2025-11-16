<?php

namespace App\Filament\Resources\OrganizationUnits\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;

class OrganizationUnitForm
{
    public static function schema(): array
    {
        return [
            Section::make('Thông tin cơ bản')
                ->description('Thông tin đơn vị/hộ tiêu thụ điện')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Tên đơn vị/Hộ tiêu thụ')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('code')
                        ->label('Mã đơn vị')
                        ->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->helperText('Mã định danh duy nhất'),

                    Select::make('parent_id')
                        ->label('Đơn vị cấp trên')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Select::make('type')
                        ->label('Loại đơn vị')
                        ->options([
                            'ORGANIZATION' => 'Tổ chức',
                            'UNIT' => 'Đơn vị',
                            'CONSUMER' => 'Hộ tiêu thụ',
                        ])
                        ->required()
                        ->default('CONSUMER'),

                    Select::make('status')
                        ->label('Trạng thái')
                        ->options([
                            'ACTIVE' => 'Hoạt động',
                            'INACTIVE' => 'Ngừng hoạt động',
                        ])
                        ->default('ACTIVE')
                        ->required(),
                ]),

            Section::make('Thông tin liên hệ')
                ->description('Thông tin người liên hệ và địa chỉ')
                ->columns(2)
                ->schema([
                    TextInput::make('contact_name')
                        ->label('Người liên hệ')
                        ->maxLength(255)
                        ->placeholder('Tên người liên hệ chính'),

                    TextInput::make('contact_phone')
                        ->label('SĐT liên hệ')
                        ->tel()
                        ->maxLength(20)
                        ->placeholder('0912345678'),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255)
                        ->nullable(),

                    Textarea::make('address')
                        ->label('Địa chỉ hộ tiêu thụ điện')
                        ->rows(2)
                        ->columnSpanFull()
                        ->placeholder('Nhập địa chỉ đầy đủ của hộ tiêu thụ'),

                    TextInput::make('building')
                        ->label('Nhà/Tòa nhà')
                        ->maxLength(100)
                        ->placeholder('VD: B1, D5, SVĐ, A17')
                        ->helperText('Tên tòa nhà hoặc khu vực'),
                ]),

            Section::make('Ghi chú')
                ->description('Thông tin bổ sung')
                ->collapsed()
                ->schema([
                    Textarea::make('notes')
                        ->label('Ghi chú')
                        ->rows(3)
                        ->columnSpanFull()
                        ->nullable(),
                ]),
        ];
    }
}
