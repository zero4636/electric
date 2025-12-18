<?php

namespace App\Filament\Resources\MeterReadings;

use App\Filament\Resources\MeterReadings\Pages\CreateMeterReading;
use App\Filament\Resources\MeterReadings\Pages\EditMeterReading;
use App\Filament\Resources\MeterReadings\Pages\ListMeterReadings;
use App\Filament\Resources\MeterReadings\Pages\ViewMeterReading;
use App\Filament\Resources\MeterReadings\Schemas\MeterReadingForm;
use App\Filament\Resources\MeterReadings\Tables\MeterReadingsTable;
use App\Models\MeterReading;
use App\Helpers\OrganizationHelper;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MeterReadingResource extends Resource
{
    protected static ?string $model = MeterReading::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    
    protected static ?string $modelLabel = 'Chỉ số công tơ';
    
    protected static ?string $pluralModelLabel = 'Chỉ số công tơ';

    protected static ?string $navigationLabel = 'Chỉ số công tơ';

    public static function getNavigationGroup(): ?string
    {
        return 'Vận hành';
    }

    protected static ?int $navigationSort = 21;

    /**
     * Scope query để chỉ hiển thị readings của meters thuộc organizations được assign
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $user = Auth::user();
        
        if (!$user || $user->role === 'super_admin') {
            return $query;
        }
        
        $orgIds = OrganizationHelper::getUserOrganizationIds($user);
        
        if (empty($orgIds)) {
            return $query->whereRaw('1 = 0');
        }
        
        // MeterReading -> ElectricMeter -> OrganizationUnit
        return $query->whereHas('electricMeter', function($meterQuery) use ($orgIds) {
            $meterQuery->whereIn('organization_unit_id', $orgIds);
        });
    }

    public static function form(Schema $schema): Schema
    {
        return MeterReadingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MeterReadingsTable::configure($table);
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
            'index' => ListMeterReadings::route('/'),
            'create' => CreateMeterReading::route('/create'),
            'view' => ViewMeterReading::route('/{record}'),
            // Edit bị tắt - chỉ số công tơ là dữ liệu lịch sử không nên sửa
            // 'edit' => EditMeterReading::route('/{record}/edit'),
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

        return $user->canManageOrganization($record->electricMeter->organizationUnit);
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
