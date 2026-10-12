<?php

namespace App\Filament\Resources\Maintenances\Schemas;

use Filament\Schemas\Schema;
use App\Models\Diagnosis;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;

class MaintenanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Liquidación de Mantenimiento')
                    ->columnSpanFull()
                    ->components([
                        Select::make('diagnosis_id')
                            ->label('Diagnóstico Previo')
                            ->options(function () {
                                return Diagnosis::with('appointment.vehicle')
                                    ->latest()
                                    ->get()
                                    ->mapWithKeys(fn ($d) => [
                                        $d->id => "Diag #{$d->id} — Placa: {$d->appointment?->vehicle?->placa}"
                                    ]);
                            })
                            ->searchable()
                            ->required(),

                        Select::make('user_id')
                            ->label('Mecánico Responsable')
                            ->relationship('technician', 'name')
                            ->default(fn () => auth()->id())
                            ->required(),

                        Select::make('maintenance_type')
                            ->label('Tipo de Mantenimiento')
                            ->options([
                                'preventivo' => 'Preventivo',
                                'correctivo' => 'Correctivo',
                            ])
                            ->required(),

                        DateTimePicker::make('entry_date')
                            ->label('Fecha/Hora Ingreso')
                            ->default(now())
                            ->required(),

                        DateTimePicker::make('delivery_date')
                            ->label('Fecha/Hora Entrega'),

                        TextInput::make('budgeted_cost')
                            ->label('Presupuesto Inicial (S/.)')
                            ->numeric()
                            ->prefix('S/.')
                            ->default(0)
                            ->live() // Reactivo al modificar presupuesto
                            ->afterStateUpdated(fn (Set $set, Get $get, $state) =>
                            $set('total_cost', (float) $state + (float) $get('labor_cost'))
                            )
                            ->required(),

                        TextInput::make('labor_cost')
                            ->label('Mano de Obra (S/.)')
                            ->numeric()
                            ->prefix('S/.')
                            ->default(0)
                            ->live() // Reactivo al modificar mano de obra
                            ->afterStateUpdated(fn (Set $set, Get $get, $state) =>
                            $set('total_cost', (float) $state + (float) $get('budgeted_cost'))
                            )
                            ->required(),

                        TextInput::make('total_cost')
                            ->label('Costo Total Liquidado (S/.)')
                            ->numeric()
                            ->prefix('S/.')
                            ->default(0)
                            ->readOnly() // Se calcula automáticamente
                            ->required(),

                        Textarea::make('procedures')
                            ->label('Procedimientos Realizados')
                            ->placeholder('Afinamiento, cambio de aceite sintético 5W-30, cambio de pastillas...')
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('observations')
                            ->label('Observaciones Técnicas')
                            ->placeholder('Recomendaciones para el cliente...')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }
}
