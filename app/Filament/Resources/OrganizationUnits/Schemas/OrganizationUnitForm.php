<?php

namespace App\Filament\Resources\OrganizationUnits\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrganizationUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin chung')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Tên đơn vị')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('Mã')
                            ->maxLength(50),

                        Select::make('parent_id')
                            ->label('Đơn vị cấp trên')
                            ->relationship('parent', 'name')
                            ->nullable(),

                        Select::make('type')
                            ->label('Loại')
                            ->options([
                                'ORGANIZATION' => 'Tổ chức',
                                'UNIT' => 'Đơn vị',
                                'CONSUMER' => 'Khách hàng',
                            ])
                            ->required(),
                    ]),

                Section::make('Thông tin liên hệ')
                    ->columns(2)
                    ->components([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->nullable(),

                        TextInput::make('contact_name')
                            ->label('Người liên hệ')
                            ->nullable(),

                        TextInput::make('contact_phone')
                            ->label('Số điện thoại')
                            ->nullable(),

                        Textarea::make('address')
                            ->label('Địa chỉ')
                            ->nullable(),
                    ]),

                Section::make('Thông tin thêm')
                    ->columns(1)
                    ->components([
                        TextInput::make('tax_code')
                            ->label('Mã số thuế')
                            ->nullable(),

                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->nullable(),

                        Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'ACTIVE' => 'Hoạt động',
                                'INACTIVE' => 'Ngừng hoạt động',
                            ])
                            ->default('ACTIVE'),
                    ]),
            ]);
    }
}
