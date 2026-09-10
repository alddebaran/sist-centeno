<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Programación de la Cita')
                    ->components([
                        // Selector de Cliente
                        Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->numero_documento} — {$record->nombres} {$record->apellidos}")
                            ->searchable()
                            ->preload()
                            ->live() // Reactividad en v5
                            ->required(),

                        // Solo muestra los vehículos del cliente seleccionado
                        Select::make('vehicle_id')
                            ->label('Vehículo')
                            ->options(function (Get $get) {
                                $clientId = $get('client_id');
                                if (! $clientId) {
                                    return [];
                                }
                                return Vehicle::where('client_id', $clientId)->pluck('placa', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->disabled(fn (Get $get) => ! $get('client_id')),

                        DatePicker::make('appointment_date')
                            ->label('Fecha')
                            ->required()
                            ->native(false)
                            ->minDate(now()->toDateString()),

                        TimePicker::make('appointment_time')
                            ->label('Hora de Atención')
                            ->required()
                            ->seconds(false)
                            ->minutesStep(30),

                        Textarea::make('reason')
                            ->label('Motivo del servicio / problema del vehículo')
                            ->placeholder('Ej: Afinamiento preventivo, revisión de frenos...')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
