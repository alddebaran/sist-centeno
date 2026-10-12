<?php

namespace App\Filament\Resources\Diagnoses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class DiagnosesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment.vehicle.placa')
                    ->label('Placa Vehículo')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('appointment.client.nombres')
                    ->label('Cliente')
                    ->formatStateUsing(fn ($record) => "{$record->appointment?->client?->nombres} {$record->appointment?->client?->apellidos}")
                    ->searchable(),

                TextColumn::make('technician.name')
                    ->label('Técnico Evaluador')
                    ->icon('heroicon-o-user')
                    ->searchable(),

                TextColumn::make('details')
                    ->label('Hallazgos / Diagnóstico')
                    ->limit(45)
                    ->tooltip(fn ($record) => $record->details)
                    ->searchable(),

                TextColumn::make('registered_at')
                    ->label('Fecha Evaluación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('registered_at', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Filtrar por Técnico')
                    ->relationship('technician', 'name'),
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
