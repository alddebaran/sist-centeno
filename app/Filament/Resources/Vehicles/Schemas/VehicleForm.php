<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Select::make('client_id')
                    ->relationship('client', 'id')
                    ->required(),
                TextInput::make('placa')
                    ->required(),
                TextInput::make('marca')
                    ->required(),
                TextInput::make('modelo')
                    ->required(),
                TextInput::make('anio'),
                TextInput::make('color'),
                TextInput::make('kilometraje'),
            ])
            ->columns(2);
    }
}
