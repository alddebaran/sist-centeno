<?php

namespace App\Filament\Resources\Diagnosticos\Pages;

use App\Filament\Resources\Diagnosticos\DiagnosticoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiagnosticos extends ListRecords
{
    protected static string $resource = DiagnosticoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
