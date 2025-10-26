<?php

namespace App\Filament\Resources\ElectricityTariffs;

use App\Filament\Resources\ElectricityTariffs\Pages\CreateElectricityTariff;
use App\Filament\Resources\ElectricityTariffs\Pages\EditElectricityTariff;
use App\Filament\Resources\ElectricityTariffs\Pages\ListElectricityTariffs;
use App\Filament\Resources\ElectricityTariffs\Schemas\ElectricityTariffForm;
use App\Filament\Resources\ElectricityTariffs\Tables\ElectricityTariffsTable;
use App\Models\ElectricityTariff;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ElectricityTariffResource extends Resource
{
    protected static ?string $model = ElectricityTariff::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    
    protected static ?string $modelLabel = 'Biểu giá điện';
    
    protected static ?string $pluralModelLabel = 'Biểu giá điện';

    protected static ?string $navigationLabel = 'Biểu giá điện';

    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý chung';
    }

    public static function form(Schema $schema): Schema
    {
        return ElectricityTariffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ElectricityTariffsTable::configure($table);
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
            'index' => ListElectricityTariffs::route('/'),
            'create' => CreateElectricityTariff::route('/create'),
            'edit' => EditElectricityTariff::route('/{record}/edit'),
        ];
    }
}
