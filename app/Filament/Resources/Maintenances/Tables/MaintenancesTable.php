<?php

namespace App\Filament\Resources\Maintenances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class MaintenancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('diagnosis.appointment.vehicle.placa')
                    ->label('Placa')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('maintenance_type')
                    ->label('Tipo de Servicio')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'preventivo' => 'info',
                        'correctivo' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('entry_date')
                    ->label('Ingreso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('delivery_date')
                    ->label('Entrega')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('En taller')
                    ->sortable(),

                TextColumn::make('total_cost')
                    ->label('Total Liquidado')
                    ->formatStateUsing(fn ($state) => 'S/. ' . number_format((float) $state, 2))
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('technician.name')
                    ->label('Mecánico')
                    ->searchable(),

                TextColumn::make('procedures')
                    ->label('Trabajos Ejecutados')
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->procedures),
            ])
            ->defaultSort('entry_date', 'desc')
            ->filters([
                SelectFilter::make('maintenance_type')
                    ->label('Tipo')
                    ->options([
                        'preventivo' => 'Preventivo',
                        'correctivo' => 'Correctivo',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
