<?php

namespace App\Filament\Resources\ElectricMeters;

use App\Filament\Resources\ElectricMeters\Pages\CreateElectricMeter;
use App\Filament\Resources\ElectricMeters\Pages\EditElectricMeter;
use App\Filament\Resources\ElectricMeters\Pages\ListElectricMeters;
use App\Filament\Resources\ElectricMeters\Schemas\ElectricMeterForm;
use App\Filament\Resources\ElectricMeters\Tables\ElectricMetersTable;
use App\Models\ElectricMeter;
use App\Helpers\OrganizationHelper;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\ElectricMeters\Schemas\ElectricMeterInfolist;
use App\Filament\Resources\ElectricMeters\Pages\ViewElectricMeter;
use Illuminate\Database\Eloquent\Builder;

class ElectricMeterResource extends Resource
{
    protected static ?string $model = ElectricMeter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static ?string $modelLabel = 'Công tơ điện';
    
    protected static ?string $pluralModelLabel = 'Công tơ điện';

    protected static ?string $navigationLabel = 'Công tơ điện';

    public static function getNavigationGroup(): ?string
    {
        return 'Danh mục';
    }

    protected static ?int $navigationSort = 14;

    /**
     * Scope query để chỉ hiển thị meters thuộc organizations mà user được assign
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        return OrganizationHelper::scopeToUserOrganizations($query);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(ElectricMeterForm::schema());
    }

    public static function table(Table $table): Table
    {
        return ElectricMetersTable::table($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ElectricMeterInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ElectricMeters\RelationManagers\MeterReadingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListElectricMeters::route('/'),
            'create' => CreateElectricMeter::route('/create'),
            'view' => ViewElectricMeter::route('/{record}'),
            'edit' => EditElectricMeter::route('/{record}/edit'),
        ];
    }

    /**
     * Authorization methods
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() || auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canView($record): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->canManageOrganization($record->organizationUnit);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() || auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return static::canView($record);
    }

    public static function canDelete($record): bool
    {
        return static::canView($record);
    }
}
