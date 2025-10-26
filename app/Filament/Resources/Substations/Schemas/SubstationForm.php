<?php

namespace App\Filament\Resources\Substations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubstationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')->label('Name')->required()->maxLength(255),
                        TextInput::make('code')->label('Code')->required()->maxLength(50),
                        TextInput::make('location')->label('Location')->nullable()->maxLength(255),
                        Select::make('status')->label('Status')->options(['ACTIVE'=>'Active','INACTIVE'=>'Inactive'])->default('ACTIVE'),
                    ]),
                Section::make('Notes')->components([
                    Textarea::make('notes')->label('Notes')->nullable(),
                ]),
            ]);
    }
}
