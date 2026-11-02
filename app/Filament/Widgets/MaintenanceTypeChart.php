<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use Filament\Widgets\ChartWidget;

class MaintenanceTypeChart extends ChartWidget
{
    protected ?string $heading = 'Distribución: Preventivo vs. Correctivo';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $preventivos = Maintenance::where('maintenance_type', 'preventivo')->count();
        $correctivos = Maintenance::where('maintenance_type', 'correctivo')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Tipo de Mantenimiento',
                    'data' => [$preventivos, $correctivos],
                    'backgroundColor' => [
                        '#3b82f6', // Azul: Preventivo
                        '#f59e0b', // Ámbar: Correctivo
                    ],
                ],
            ],
            'labels' => ['Preventivo', 'Correctivo'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
