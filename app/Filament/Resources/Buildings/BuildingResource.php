<?php

namespace App\Filament\Resources\Buildings;

use App\Filament\Resources\Buildings\Pages\CreateBuilding;
use App\Filament\Resources\Buildings\Pages\EditBuilding;
use App\Filament\Resources\Buildings\Pages\ListBuildings;
use App\Filament\Resources\Buildings\Pages\ViewBuilding;
use App\Filament\Resources\Buildings\Schemas\BuildingForm;
use App\Filament\Resources\Buildings\Schemas\BuildingInfolist;
use App\Filament\Resources\Buildings\Tables\BuildingsTable;
use App\Models\Building;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Tòa nhà';

    protected static ?string $modelLabel = 'Tòa nhà';

    protected static ?string $pluralModelLabel = 'Tòa nhà';

    protected static UnitEnum|string|null $navigationGroup = 'Danh mục';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return BuildingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BuildingsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BuildingInfolist::configure($schema);
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
            'index' => ListBuildings::route('/'),
            'create' => CreateBuilding::route('/create'),
            'edit' => EditBuilding::route('/{record}/edit'),
            'view' => ViewBuilding::route('/{record}'),
        ];
    }
}
