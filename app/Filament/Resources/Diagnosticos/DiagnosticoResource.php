<?php

namespace App\Filament\Resources\Diagnosticos;

use App\Filament\Resources\Diagnosticos\Pages\CreateDiagnostico;
use App\Filament\Resources\Diagnosticos\Pages\EditDiagnostico;
use App\Filament\Resources\Diagnosticos\Pages\ListDiagnosticos;
use App\Filament\Resources\Diagnosticos\Schemas\DiagnosticoForm;
use App\Filament\Resources\Diagnosticos\Tables\DiagnosticosTable;
use App\Models\Diagnostico;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DiagnosticoResource extends Resource
{
    protected static ?string $model = Diagnostico::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Diagnostico';

    public static function form(Schema $schema): Schema
    {
        return DiagnosticoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiagnosticosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiagnosticos::route('/'),
            'create' => CreateDiagnostico::route('/create'),
            'edit' => EditDiagnostico::route('/{record}/edit'),
        ];
    }
}
