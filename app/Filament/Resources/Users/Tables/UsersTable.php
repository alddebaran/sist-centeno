<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Password;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('role')
                    ->label('Rol Asignado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'recepcionista' => 'info',
                        'tecnico' => 'warning',
                        'cliente' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrador',
                        'recepcionista' => 'Recepcionista',
                        'tecnico' => 'Técnico Mecánico',
                        'cliente' => 'Cliente',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                // Columna de Estado
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->label('Filtrar por Rol')
                    ->options([
                        'admin' => 'Administrador',
                        'recepcionista' => 'Recepcionista',
                        'tecnico' => 'Técnico Mecánico',
                        'cliente' => 'Cliente',
                    ]),
            ])
            ->recordActions([
                Action::make('reset_password')
                    ->label('Restablecer Clave')
                    ->icon(Heroicon::OutlinedKey)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reenviar enlace de cambio de contraseña')
                    ->modalDescription(fn (User $record) => "Se enviará la solicitud de restablecimiento a {$record->email}. Si el usuario no puede abrir su correo, podrás copiar el enlace generado en pantalla.")
                    ->action(function (User $record) {
                        // Genera el token oficial del broker de Laravel
                        $token = Password::broker()->createToken($record);

                        // Intenta enviar la notificación oficial por correo
                        Password::broker()->sendResetLink(['email' => $record->email]);

                        // Enlace directo de recuperación (para copiar y pegar si se necesita asistir al usuario)
                        $resetUrl = url(route('password.reset', [
                            'token' => $token,
                            'email' => $record->email,
                        ], false));

                        Notification::make()
                            ->title('Enlace generado con éxito')
                            ->body("Correo enviado a {$record->email}. Enlace directo: {$resetUrl}")
                            ->success()
                            ->duration(10000)
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
