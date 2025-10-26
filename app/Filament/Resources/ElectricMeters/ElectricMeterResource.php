<?php

namespace App\Filament\Resources\ElectricMeters;

use App\Filament\Resources\ElectricMeters\Pages\CreateElectricMeter;
use App\Filament\Resources\ElectricMeters\Pages\EditElectricMeter;
use App\Filament\Resources\ElectricMeters\Pages\ListElectricMeters;
use App\Filament\Resources\ElectricMeters\Schemas\ElectricMeterForm;
use App\Filament\Resources\ElectricMeters\Tables\ElectricMetersTable;
use App\Models\ElectricMeter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ElectricMeterResource extends Resource
{
    protected static ?string $model = ElectricMeter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'Công tơ điện';
    
    protected static ?string $pluralModelLabel = 'Công tơ điện';

    protected static ?string $navigationLabel = 'Công tơ điện';

    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý thiết bị';
    }

    public static function form(Schema $schema): Schema
    {
        return ElectricMeterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ElectricMetersTable::configure($table);
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
            'index' => ListElectricMeters::route('/'),
            'create' => CreateElectricMeter::route('/create'),
            'edit' => EditElectricMeter::route('/{record}/edit'),
        ];
    }
}
