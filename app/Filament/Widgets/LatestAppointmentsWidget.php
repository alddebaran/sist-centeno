<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAppointmentsWidget extends BaseWidget
{
    protected static ?string $heading = 'Citas de Hoy y Órdenes en Espera';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::with(['client', 'vehicle'])
                    ->latest('appointment_date')
                    ->take(6)
            )
            ->columns([
                TextColumn::make('appointment_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->icon('heroicon-o-calendar')
                    ->sortable(),

                TextColumn::make('appointment_time')
                    ->label('Hora')
                    ->time('h:i A'),

                TextColumn::make('vehicle.placa')
                    ->label('Placa')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('client.nombres')
                    ->label('Propietario')
                    ->formatStateUsing(fn ($record) => "{$record->client?->nombres} {$record->client?->apellidos}"),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmada' => 'info',
                        'finalizada' => 'success',
                        'cancelada' => 'danger',
                        'reprogramada' => 'primary',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                TextColumn::make('reason')
                    ->label('Motivo / Falla Reportada')
                    ->limit(40),
            ])
            ->paginated(false);
    }
}
