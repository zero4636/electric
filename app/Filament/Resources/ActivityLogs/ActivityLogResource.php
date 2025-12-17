<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogs\Pages\ViewActivityLog;
use Spatie\Activitylog\Models\Activity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\KeyValueEntry;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    
    protected static ?string $modelLabel = 'Nhật ký hoạt động';
    
    protected static ?string $pluralModelLabel = 'Nhật ký hoạt động';
    
    protected static ?string $navigationLabel = 'Nhật ký hoạt động';
    
    public static function getNavigationGroup(): ?string
    {
        return 'Cài đặt';
    }
    
    protected static ?int $navigationSort = 100;

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin hoạt động')
                    ->columns(2)
                    ->components([
                        TextEntry::make('description')
                            ->label('Mô tả')
                            ->columnSpanFull(),
                        TextEntry::make('causer.name')
                            ->label('Người thực hiện')
                            ->placeholder('Hệ thống'),
                        TextEntry::make('created_at')
                            ->label('Thời gian')
                            ->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('subject_type')
                            ->label('Loại đối tượng')
                            ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '—'),
                        TextEntry::make('subject_id')
                            ->label('ID đối tượng'),
                    ]),
                Section::make('Chi tiết thay đổi')
                    ->columns(1)
                    ->visible(fn ($record) => !empty($record->properties) && count($record->properties) > 0)
                    ->components([
                        TextEntry::make('properties')
                            ->label('Dữ liệu')
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) {
                                    return '—';
                                }
                                return '<pre class="text-sm">' . json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                            })
                            ->html(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Hoạt động')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('causer.name')
                    ->label('Người thực hiện')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Hệ thống'),
                TextColumn::make('subject_type')
                    ->label('Đối tượng')
                    ->badge()
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
            'view' => ViewActivityLog::route('/{record}'),
        ];
    }
    
    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }
    
    public static function canView($record): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
    
    public static function canEdit($record): bool
    {
        return false;
    }
    
    public static function canDelete($record): bool
    {
        return false;
    }
}
