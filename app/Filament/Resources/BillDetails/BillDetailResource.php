<?php

namespace App\Filament\Resources\BillDetails;

use App\Filament\Resources\BillDetails\Pages\CreateBillDetail;
use App\Filament\Resources\BillDetails\Pages\EditBillDetail;
use App\Filament\Resources\BillDetails\Pages\ListBillDetails;
use App\Filament\Resources\BillDetails\Schemas\BillDetailForm;
use App\Filament\Resources\BillDetails\Tables\BillDetailsTable;
use App\Models\BillDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BillDetailResource extends Resource
{
    protected static ?string $model = BillDetail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BillDetailForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BillDetailsTable::configure($table);
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
            'index' => ListBillDetails::route('/'),
            'create' => CreateBillDetail::route('/create'),
            'edit' => EditBillDetail::route('/{record}/edit'),
        ];
    }
}
