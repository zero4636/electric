<?php

namespace App\Filament\Resources\OrganizationUnits;

use App\Filament\Resources\OrganizationUnits\Pages\CreateOrganizationUnit;
use App\Filament\Resources\OrganizationUnits\Pages\EditOrganizationUnit;
use App\Filament\Resources\OrganizationUnits\Pages\ListOrganizationUnits;
use App\Filament\Resources\OrganizationUnits\Pages\ViewOrganizationUnit;
use App\Filament\Resources\OrganizationUnits\Schemas\OrganizationUnitForm;
use App\Filament\Resources\OrganizationUnits\Schemas\OrganizationUnitInfolist;
use App\Filament\Resources\OrganizationUnits\Tables\OrganizationUnitsTable;
use App\Models\OrganizationUnit;
use App\Helpers\OrganizationHelper;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\OrganizationUnits\RelationManagers\ElectricMetersRelationManager;
use App\Filament\Resources\OrganizationUnits\RelationManagers\BillsRelationManager;
use App\Filament\Resources\OrganizationUnits\RelationManagers\ChildrenRelationManager;
use Illuminate\Database\Eloquent\Builder;

class OrganizationUnitResource extends Resource
{
    protected static ?string $model = OrganizationUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'Đơn vị tổ chức';
    
    protected static ?string $pluralModelLabel = 'Đơn vị tổ chức';

    protected static ?string $navigationLabel = 'Đơn vị tổ chức';

    public static function getNavigationGroup(): ?string
    {
        return 'Danh mục';
    }

    protected static ?int $navigationSort = 11;

    /**
     * Scope query để chỉ hiển thị organizations mà user được assign
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        return OrganizationHelper::scopeOrganizationUnitsToUser($query);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(OrganizationUnitForm::schema());
    }

    public static function table(Table $table): Table
    {
        return OrganizationUnitsTable::table($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrganizationUnitInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            ChildrenRelationManager::class,
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
            'view' => ViewOrganizationUnit::route('/{record}'),
        ];
    }
}
