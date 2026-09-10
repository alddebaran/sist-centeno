<?php

namespace App\Filament\Resources\Appointments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('appointment_time')
                    ->label('Hora')
                    ->time('h:i A'),

                TextColumn::make('client.nombres')
                    ->label('Cliente')
                    ->formatStateUsing(fn ($record) => "{$record->client?->nombres} {$record->client?->apellidos}")
                    ->searchable(),

                TextColumn::make('vehicle.placa')
                    ->label('Placa')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'confirmada' => 'info',
                        'reprogramada' => 'primary',
                        'cancelada' => 'danger',
                        'finalizada' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(30),
            ])
            ->defaultSort('appointment_date', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'confirmada' => 'Confirmada',
                        'reprogramada' => 'Reprogramada',
                        'cancelada' => 'Cancelada',
                    ]),

                Filter::make('appointment_date')
                    ->form([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'], fn ($q, $date) => $q->whereDate('appointment_date', '>=', $date))
                            ->when($data['hasta'], fn ($q, $date) => $q->whereDate('appointment_date', '<=', $date));
                    }),
            ])
            ->recordActions([
                // Botón Confirmar
                Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('info')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['pendiente', 'reprogramada']))
                    ->action(fn (Appointment $record) => $record->update(['status' => 'confirmada'])),

                // Botón Reprogramar
                Action::make('reprogramar')
                    ->label('Reprogramar')
                    ->icon(Heroicon::OutlinedClock)
                    ->color('warning')
                    ->visible(fn (Appointment $record) => $record->status !== 'cancelada')
                    ->form([
                        DatePicker::make('appointment_date')->label('Nueva Fecha')->required(),
                        TimePicker::make('appointment_time')->label('Nueva Hora')->required()->seconds(false),
                    ])
                    ->action(function (Appointment $record, array $data) {
                        $record->update([
                            'appointment_date' => $data['appointment_date'],
                            'appointment_time' => $data['appointment_time'],
                            'status' => 'reprogramada',
                        ]);
                    }),

                // Botón Cancelar
                Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Appointment $record) => $record->status !== 'cancelada')
                    ->form([
                        Textarea::make('cancellation_reason')->label('Motivo de Cancelación')->required(),
                    ])
                    ->action(function (Appointment $record, array $data) {
                        $record->update([
                            'status' => 'cancelada',
                            'cancellation_reason' => $data['cancellation_reason'],
                        ]);
                    }),

                EditAction::make(),
            ]);
    }
}
