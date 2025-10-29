<?php

namespace App\Filament\Resources\MeterReadings;

use App\Filament\Resources\MeterReadings\Pages\CreateMeterReading;
use App\Filament\Resources\MeterReadings\Pages\EditMeterReading;
use App\Filament\Resources\MeterReadings\Pages\ListMeterReadings;
use App\Filament\Resources\MeterReadings\Schemas\MeterReadingForm;
use App\Filament\Resources\MeterReadings\Tables\MeterReadingsTable;
use App\Models\MeterReading;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

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
            'edit' => EditMeterReading::route('/{record}/edit'),
        ];
    }
}
