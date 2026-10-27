<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use App\Filament\Exports\ClientExporter;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(ClientExporter::class)
                ->label('Exportar Clientes')
                ->color('success'),

            CreateAction::make(),
        ];
    }
}
