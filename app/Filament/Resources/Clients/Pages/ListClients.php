<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Exportar a Excel (.xlsx)')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('success')
                ->action(function () {
                    $clients = Client::withCount('vehicles')->get();
                    $filename = 'reporte_clientes_' . now()->format('Ymd_His') . '.xlsx'; // Extensión XLSX

                    // 1. Asegurar la carpeta pública de almacenamiento
                    $directory = storage_path('app/public/reportes');
                    if (! file_exists($directory)) {
                        mkdir($directory, 0755, true);
                    }

                    $filePath = $directory . '/' . $filename;

                    // 2. Crear el libro XLSX con OpenSpout
                    $writer = new Writer();
                    $writer->openToFile($filePath);

                    // Fila de encabezados
                    $writer->addRow(Row::fromValues([
                        'Tipo Documento',
                        'N° Documento',
                        'Nombres',
                        'Apellidos',
                        'Teléfono / Celular',
                        'Correo Electrónico',
                        'Dirección',
                        'Total Vehículos',
                        'Fecha Registro',
                    ]));

                    // Filas de datos
                    foreach ($clients as $client) {
                        $writer->addRow(Row::fromValues([
                            $client->tipo_documento,
                            $client->numero_documento,
                            $client->nombres,
                            $client->apellidos,
                            $client->telefono ?? 'S/N',
                            $client->email ?? 'S/N',
                            $client->direccion ?? 'S/N',
                            $client->vehicles_count,
                            $client->created_at?->format('d/m/Y H:i'),
                        ]));
                    }

                    $writer->close();

                    // URL pública directa
                    $url = asset('storage/reportes/' . $filename);

                    // 3. Notificación interactiva con enlace para abrir o descargar
                    Notification::make()
                        ->title('Libro Excel (.xlsx) Generado')
                        ->body("Se exportaron {$clients->count()} clientes. Haz clic abajo para descargar el archivo de Excel.")
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->persistent()
                        ->actions([
                            Action::make('download')
                                ->label('Descargar Excel (.xlsx)')
                                ->button()
                                ->color('success')
                                ->url($url, shouldOpenInNewTab: true),
                        ])
                        ->send();
                }),

            CreateAction::make(),
        ];
    }
}
