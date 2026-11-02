<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ServicesChart extends ChartWidget
{
    protected ?string $heading = 'Evolución de Atenciones en Taller (Últimos 6 Meses)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $meses = [];
        $totales = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $meses[] = ucfirst($mes->translatedFormat('M'));
            $totales[] = Appointment::whereMonth('appointment_date', $mes->month)
                ->whereYear('appointment_date', $mes->year)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Citas y Servicios',
                    'data' => $totales,
                    'borderColor' => '#d97706', // Ámbar Corporativo
                    'backgroundColor' => 'rgba(217, 119, 6, 0.1)',
                    'fill' => 'start',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $meses,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
