<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la Cuenta')
                    ->description('Credenciales de acceso y asignación de rol en el taller')
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('name')
                            ->label('Nombre Completo')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),

                        Select::make('role')
                            ->label('Rol / Permisos del Sistema')
                            ->options([
                                'admin' => '👑 Administrador (Acceso Total)',
                                'recepcionista' => '📋 Recepcionista (Clientes, Vehículos y Agenda)',
                                'tecnico' => '🔧 Técnico Mecánico (Diagnósticos y Mantenimientos)',
                                'cliente' => '👤 Cliente (Acceso solo al Portal Web)',
                            ])
                            ->default('recepcionista')
                            ->required(),

                        // Selector de Estado de la Cuenta
                        Select::make('status')
                            ->label('Estado de la Cuenta')
                            ->options([
                                'activo' => '✅ Activo (Puede ingresar al sistema)',
                                'inactivo' => '⛔ Inactivo (Acceso suspendido)',
                            ])
                            ->default('activo')
                            ->required(),

                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            // Solo es obligatorio al crear un nuevo usuario
                            ->required(fn (string $operation): bool => $operation === 'create')
                            // Si se deja en blanco al editar, no se modifica la contraseña
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8)
                            ->helperText('Debe contener como mínimo 8 caracteres. Déjalo en blanco si no deseas cambiarla al editar.'),
                    ])
                    ->columns(2),
            ]);
    }
}
