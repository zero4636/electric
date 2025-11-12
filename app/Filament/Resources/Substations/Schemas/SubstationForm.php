<?php

namespace App\Filament\Resources\Substations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubstationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin trạm biến áp / Khu vực')
                    ->description('Nhập thông tin trạm biến áp')
                    ->icon('heroicon-o-bolt')
                    ->columns(2)
                    ->components([
                        TextInput::make('code')
                            ->label('Mã trạm')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('Ví dụ: B1, ĐLK, KTX')
                            ->columnSpan(1),
                        
                        TextInput::make('name')
                            ->label('Tên trạm')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Tên đầy đủ của trạm biến áp')
                            ->columnSpan(1),

                        TextInput::make('location')
                            ->label('Khu vực')
                            ->maxLength(500)
                            ->placeholder('VD: Khu vực B1, Ký túc xá')
                            ->columnSpan(1),

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

                        Textarea::make('address')
                            ->label('Địa chỉ chi tiết')
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder('Địa chỉ cụ thể của trạm biến áp')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
