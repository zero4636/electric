<?php

namespace App\Filament\Resources\Substations;

use App\Filament\Resources\Substations\Pages\CreateSubstation;
use App\Filament\Resources\Substations\Pages\EditSubstation;
use App\Filament\Resources\Substations\Pages\ListSubstations;
use App\Filament\Resources\Substations\Pages\ViewSubstation;
use App\Filament\Resources\Substations\Schemas\SubstationForm;
use App\Filament\Resources\Substations\Schemas\SubstationInfolist;
use App\Filament\Resources\Substations\Tables\SubstationsTable;
use App\Models\Substation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubstationResource extends Resource
{
    protected static ?string $model = Substation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $modelLabel = 'Trạm điện';
    
    protected static ?string $pluralModelLabel = 'Trạm điện';

    protected static ?string $navigationLabel = 'Trạm điện';

    public static function getNavigationGroup(): ?string
    {
        return 'Danh mục';
    }

    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        return SubstationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubstationsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubstationInfolist::configure($schema);
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
            'index' => ListSubstations::route('/'),
            'create' => CreateSubstation::route('/create'),
            'edit' => EditSubstation::route('/{record}/edit'),
            'view' => ViewSubstation::route('/{record}'),
        ];
    }
}
