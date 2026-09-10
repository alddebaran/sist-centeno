<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Cliente')
                    ->description('Datos personales o de contacto del propietario')
                    ->components([
                        Select::make('tipo_documento')
                            ->label('Tipo de Documento')
                            ->options([
                                'DNI' => 'DNI',
                                'RUC' => 'RUC',
                                'CE' => 'Carné Extranjería',
                            ])
                            ->default('DNI')
                            ->required(),

                        TextInput::make('numero_documento')
                            ->label('N° Documento')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(15),

                        TextInput::make('nombres')
                            ->label('Nombres')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('apellidos')
                            ->label('Apellidos')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('telefono')
                            ->label('Teléfono / Celular')
                            ->tel()
                            ->maxLength(15),

                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->maxLength(100),

                        TextInput::make('direccion')
                            ->label('Dirección')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
