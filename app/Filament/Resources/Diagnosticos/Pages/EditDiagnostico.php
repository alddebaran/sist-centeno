<?php

namespace App\Filament\Resources\Diagnosticos\Pages;

use App\Filament\Resources\Diagnosticos\DiagnosticoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiagnostico extends EditRecord
{
    protected static string $resource = DiagnosticoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
