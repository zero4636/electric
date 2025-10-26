<?php

namespace App\Filament\Resources\OrganizationUnits;

use App\Filament\Resources\OrganizationUnits\Pages\CreateOrganizationUnit;
use App\Filament\Resources\OrganizationUnits\Pages\EditOrganizationUnit;
use App\Filament\Resources\OrganizationUnits\Pages\ListOrganizationUnits;
use App\Filament\Resources\OrganizationUnits\Schemas\OrganizationUnitForm;
use App\Filament\Resources\OrganizationUnits\Tables\OrganizationUnitsTable;
use App\Models\OrganizationUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\OrganizationUnits\RelationManagers\ElectricMetersRelationManager;
use App\Filament\Resources\OrganizationUnits\RelationManagers\BillsRelationManager;

class OrganizationUnitResource extends Resource
{
    protected static ?string $model = OrganizationUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'Đơn vị tổ chức';
    
    protected static ?string $pluralModelLabel = 'Đơn vị tổ chức';

    protected static ?string $navigationLabel = 'Đơn vị tổ chức';

    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý chung';
    }

    public static function form(Schema $schema): Schema
    {
        return OrganizationUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ElectricMetersRelationManager::class,
            BillsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizationUnits::route('/'),
            'create' => CreateOrganizationUnit::route('/create'),
            'edit' => EditOrganizationUnit::route('/{record}/edit'),
        ];
    }
}
