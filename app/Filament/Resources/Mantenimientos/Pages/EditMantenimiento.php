<?php

namespace App\Filament\Resources\Mantenimientos\Pages;

use App\Filament\Resources\Mantenimientos\MantenimientoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMantenimiento extends EditRecord
{
    protected static string $resource = MantenimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
