<?php

namespace App\Filament\Resources\Diagnoses\Schemas;

use Filament\Schemas\Schema;
use App\Models\Appointment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;


class DiagnosisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Evaluación Técnica del Vehículo')
                    ->columnSpanFull()
                    ->components([
                        Select::make('appointment_id')
                            ->label('Cita / Vehículo')
                            ->options(function () {
                                return Appointment::with(['client', 'vehicle'])
                                    ->latest()
                                    ->get()
                                    ->mapWithKeys(fn ($a) => [
                                        $a->id => "Cita #{$a->id} — Placa: {$a->vehicle?->placa} ({$a->client?->nombres} {$a->client?->apellidos})"
                                    ]);
                            })
                            ->searchable()
                            ->required(),

                        Select::make('user_id')
                            ->label('Mecánico Evaluador')
                            ->relationship('technician', 'name')
                            ->default(fn () => auth()->id())
                            ->required(),

                        Textarea::make('details')
                            ->label('Diagnóstico Técnico Detallado')
                            ->placeholder('Detalle de fallas identificadas, escaneo computarizado, piezas desgastadas...')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
