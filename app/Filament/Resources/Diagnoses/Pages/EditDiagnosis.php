<?php

namespace App\Filament\Resources\Diagnoses\Pages;

use App\Filament\Resources\Diagnoses\DiagnosisResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiagnosis extends EditRecord
{
    protected static string $resource = DiagnosisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
