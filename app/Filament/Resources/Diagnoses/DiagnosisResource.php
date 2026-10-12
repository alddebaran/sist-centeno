<?php

namespace App\Filament\Resources\Diagnoses;

use App\Filament\Resources\Diagnoses\Pages\CreateDiagnosis;
use App\Filament\Resources\Diagnoses\Pages\EditDiagnosis;
use App\Filament\Resources\Diagnoses\Pages\ListDiagnoses;
use App\Filament\Resources\Diagnoses\Schemas\DiagnosisForm;
use App\Filament\Resources\Diagnoses\Tables\DiagnosesTable;
use App\Models\Diagnosis;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


class DiagnosisResource extends Resource
{
    protected static ?string $model = Diagnosis::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Diagnósticos';

    protected static ?string $modelLabel = 'Diagnóstico';

    // Control de roles: Solo Admin y Técnico
    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->isTecnico());
    }

    public static function form(Schema $schema): Schema
    {
        return DiagnosisForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiagnosesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiagnoses::route('/'),
            'create' => CreateDiagnosis::route('/create'),
            'edit' => EditDiagnosis::route('/{record}/edit'),
        ];
    }
}
