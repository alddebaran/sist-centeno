<?php

namespace App\Filament\Resources\Vehicles\Tables;

use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('placa')
                    ->label('Placa')
                    ->badge()
                    ->color('primary')
                    ->searchable() // Búsqueda global por placa (CP-F-13)
                    ->sortable(),

                TextColumn::make('marca')
                    ->searchable(),

                TextColumn::make('modelo')
                    ->searchable(),

                TextColumn::make('client.nombres')
                    ->label('Propietario')
                    ->formatStateUsing(fn ($record) => "{$record->client?->nombres} {$record->client?->apellidos}")
                    ->searchable(),
                // Contador y ordenamiento por más atenciones
                TextColumn::make('appointments_count')
                    ->counts('appointments') // Cuenta las citas automáticamente
                    ->label('Atenciones')
                    ->badge()
                    ->color('info')
                    ->sortable(), // Permite ordenar de mayor a menor y viceversa
                TextColumn::make('anio')
                    ->label('Año')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                // CP-F-13 / RF11: Historia Vehicular (HEV) 100% NATIVA EN FILAMENT
                Action::make('hev')
                    ->label('Historia Vehicular (HEV)')
                    ->icon(Heroicon::OutlinedClock)
                    ->color('success')
                    ->modalHeading(fn (Vehicle $record) => "Historia Electrónica: {$record->marca} {$record->modelo} — Placa [{$record->placa}]")
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false) // Solo lectura
                    ->infolist([
                        // 1. BARRA SUPERIOR DE KPIS RÁPIDOS
                        Section::make()
                            ->schema([
                                TextEntry::make('appointments_count')
                                    ->counts('appointments')
                                    ->label('Total Visitas a Taller')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-wrench'),

                                TextEntry::make('inversion_total')
                                    ->label('Inversión Acumulada')
                                    ->state(function (Vehicle $record) {
                                        $total = 0;
                                        foreach ($record->appointments as $app) {
                                            foreach ($app->diagnoses as $diag) {
                                                if ($diag->maintenance) {
                                                    $total += (float) $diag->maintenance->total_cost;
                                                }
                                            }
                                        }
                                        return 'S/. ' . number_format($total, 2);
                                    })
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-currency-dollar'),

                                TextEntry::make('ultima_atencion')
                                    ->label('Última Atención Registrada')
                                    ->state(fn (Vehicle $record) => $record->appointments->first()?->appointment_date ? \Carbon\Carbon::parse($record->appointments->first()->appointment_date)->format('d/m/Y') : 'Sin atenciones')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-o-calendar'),
                            ])
                            ->columns(3),

                        // 2. PESTAÑAS (TABS NATIVAS DE FILAMENT)
                        Tabs::make('Historia_Vehicular')
                            ->tabs([
                                // PESTAÑA 1: LÍNEA DE TIEMPO CRONOLÓGICA
                                Tab::make('Línea de Tiempo')
                                    ->icon(Heroicon::OutlinedClock)
                                    ->badge(fn (Vehicle $record) => $record->appointments->count())
                                    ->schema([
                                        RepeatableEntry::make('appointments')
                                            ->label('Atenciones Realizadas (Más reciente primero)')
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('appointment_date')
                                                            ->label('Fecha')
                                                            ->date('d/m/Y')
                                                            ->icon('heroicon-o-calendar')
                                                            ->weight('bold'),

                                                        TextEntry::make('appointment_time')
                                                            ->label('Hora Turno')
                                                            ->time('h:i A'),

                                                        TextEntry::make('status')
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
                                                    ]),

                                                TextEntry::make('reason')
                                                    ->label('Falla Reportada por el Cliente')
                                                    ->columnSpanFull(),

                                                // Diagnósticos y Liquidación de esta cita
                                                RepeatableEntry::make('diagnoses')
                                                    ->label('Evaluación Técnica y Liquidación')
                                                    ->schema([
                                                        TextEntry::make('technician.name')
                                                            ->label('Técnico Mecánico')
                                                            ->icon('heroicon-o-user'),

                                                        TextEntry::make('details')
                                                            ->label('Diagnóstico Técnico'),

                                                        TextEntry::make('maintenance.maintenance_type')
                                                            ->label('Mantenimiento')
                                                            ->badge()
                                                            ->color(fn ($state) => $state === 'preventivo' ? 'info' : 'warning')
                                                            ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : 'Pendiente')
                                                            ->placeholder('Sin liquidar'),

                                                        TextEntry::make('maintenance.total_cost')
                                                            ->label('Costo Final Liquidado')
                                                            ->formatStateUsing(fn ($state) => $state ? 'S/. ' . number_format((float) $state, 2) : 'S/. 0.00')
                                                            ->weight('bold'),

                                                        TextEntry::make('maintenance.procedures')
                                                            ->label('Procedimientos Ejecutados')
                                                            ->columnSpanFull()
                                                            ->placeholder('En proceso en taller'),

                                                        TextEntry::make('maintenance.observations')
                                                            ->label('Observaciones Técnicas')
                                                            ->columnSpanFull()
                                                            ->placeholder('Sin observaciones'),
                                                    ])
                                                    ->columns(4)
                                                    ->columnSpanFull(),
                                            ])
                                            ->columnSpanFull(),
                                    ]),

                                // PESTAÑA 2: RESUMEN DE MANTENIMIENTOS Y PROCEDIMIENTOS
                                Tab::make('Historial de Trabajos')
                                    ->icon(Heroicon::OutlinedWrenchScrewdriver)
                                    ->schema([
                                        RepeatableEntry::make('appointments')
                                            ->label('Lista Consolidada de Procedimientos')
                                            ->schema([
                                                RepeatableEntry::make('diagnoses')
                                                    ->label('Diagnostico')
                                                    ->schema([
                                                        TextEntry::make('maintenance.maintenance_type')
                                                            ->label('Tipo')
                                                            ->badge(),
                                                        TextEntry::make('maintenance.procedures')
                                                            ->label('Trabajos y Repuestos')
                                                            ->columnSpan(2),
                                                        TextEntry::make('maintenance.total_cost')
                                                            ->label('Costo')
                                                            ->formatStateUsing(fn ($state) => $state ? 'S/. ' . number_format((float) $state, 2) : '—'),
                                                    ])
                                                    ->columns(4)
                                                    ->columnSpanFull(),
                                            ])
                                            ->columnSpanFull(),
                                    ]),

                                // PESTAÑA 3: FICHA DEL VEHÍCULO Y PROPIETARIO
                                Tab::make('Ficha Técnica y Contacto')
                                    ->icon(Heroicon::OutlinedIdentification)
                                    ->schema([
                                        Section::make('Datos del Propietario')
                                            ->schema([
                                                TextEntry::make('client.numero_documento')->label('N° Documento (DNI/RUC)'),
                                                TextEntry::make('client.nombres')->label('Nombres')->formatStateUsing(fn ($record) => "{$record->client?->nombres} {$record->client?->apellidos}"),
                                                TextEntry::make('client.telefono')->label('Teléfono / Celular')->placeholder('No registrado'),
                                                TextEntry::make('client.email')->label('Correo Electrónico')->placeholder('No registrado'),
                                                TextEntry::make('client.direccion')->label('Dirección')->placeholder('No registrada')->columnSpanFull(),
                                            ])->columns(2),

                                        Section::make('Especificaciones de la Unidad')
                                            ->schema([
                                                TextEntry::make('placa')->label('Placa')->badge(),
                                                TextEntry::make('marca')->label('Marca'),
                                                TextEntry::make('modelo')->label('Modelo'),
                                                TextEntry::make('anio')->label('Año de Fabricación')->placeholder('S/A'),
                                                TextEntry::make('color')->label('Color')->placeholder('No especificado'),
                                                TextEntry::make('kilometraje')->label('Último Kilometraje')->placeholder('No registrado'),
                                            ])->columns(3),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                EditAction::make(),
            ])
            ->filters([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
