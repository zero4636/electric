<?php

namespace App\Filament\Resources\TariffTypes;

use App\Filament\Resources\TariffTypes\Pages\CreateTariffType;
use App\Filament\Resources\TariffTypes\Pages\EditTariffType;
use App\Filament\Resources\TariffTypes\Pages\ListTariffTypes;
use App\Filament\Resources\TariffTypes\Schemas\TariffTypeForm;
use App\Filament\Resources\TariffTypes\Tables\TariffTypesTable;
use App\Models\TariffType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TariffTypeResource extends Resource
{
    protected static ?string $model = TariffType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Loại biểu giá';

    protected static ?string $modelLabel = 'Loại biểu giá';

    protected static ?string $pluralModelLabel = 'Loại biểu giá';

    protected static UnitEnum|string|null $navigationGroup = 'Biểu giá';

    protected static ?int $navigationSort = 41;

    public static function form(Schema $schema): Schema
    {
        return TariffTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TariffTypesTable::configure($table);
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
            'index' => ListTariffTypes::route('/'),
            'create' => CreateTariffType::route('/create'),
            'edit' => EditTariffType::route('/{record}/edit'),
        ];
    }
}
