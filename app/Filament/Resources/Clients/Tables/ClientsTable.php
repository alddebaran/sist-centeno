<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_documento')
                    ->label('Documento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nombres')
                    ->label('Nombres')
                    ->searchable(),

                TextColumn::make('apellidos')
                    ->label('Apellidos')
                    ->searchable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),

                // Contador de vehículos asociados
                TextColumn::make('vehicles_count')
                    ->counts('vehicles')
                    ->label('Vehículos')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                //
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
