<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehiclesRelationManager extends RelationManager
{
    protected static string $relationship = 'vehicles';
    protected static ?string $title = 'Vehículos del Cliente';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('placa')
                    ->label('Placa vehicular')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->extraInputAttributes(['style' => 'text-transform: uppercase']),

                TextInput::make('marca')
                    ->required(),

                TextInput::make('modelo')
                    ->required(),

                TextInput::make('anio')
                    ->label('Año')
                    ->numeric(),

                TextInput::make('color'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('placa')
            ->columns([
                TextColumn::make('placa')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('marca')
                    ->searchable(),
                TextColumn::make('modelo')
                    ->searchable(),
                TextColumn::make('anio')
                    ->label('Año')
                    ->searchable(),
                TextColumn::make('color')
                    ->searchable(),
                TextColumn::make('kilometraje')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('Agregar Vehículo'),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
