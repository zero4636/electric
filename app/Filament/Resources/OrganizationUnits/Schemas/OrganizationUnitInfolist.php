<?php

namespace App\Filament\Resources\OrganizationUnits\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;

class OrganizationUnitInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->components([
                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('code')->label('Code'),
                        TextEntry::make('parent.name')->label('Parent Unit'),
                        TextEntry::make('type')->label('Type'),
                        TextEntry::make('status')->label('Status'),
                    ]),
                Section::make('Contact')
                    ->components([
                        TextEntry::make('email')->label('Email'),
                        TextEntry::make('contact_name')->label('Contact Name'),
                        TextEntry::make('contact_phone')->label('Contact Phone'),
                        TextEntry::make('address')->label('Address'),
                    ]),
                Section::make('Additional')
                    ->components([
                        TextEntry::make('tax_code')->label('Tax Code'),
                        TextEntry::make('notes')->label('Notes'),
                    ]),
            ]);
    }
}
