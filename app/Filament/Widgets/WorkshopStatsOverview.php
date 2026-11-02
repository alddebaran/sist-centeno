<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WorkshopStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total facturado este mes
        $ingresosMes = Maintenance::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_cost');

        $citasPendientes = Appointment::whereIn('status', ['pendiente', 'reprogramada'])->count();
        $mantenimientosTotal = Maintenance::count();
        $vehiculosTotal = Vehicle::count();

        return [
            Stat::make('Citas por Atender', $citasPendientes)
                ->description('Pendientes de recepción / turno')
                ->descriptionIcon('heroicon-m-clock')
                ->color($citasPendientes > 0 ? 'warning' : 'success')
                ->chart([2, 4, 6, 3, 5, 8, $citasPendientes]),

            Stat::make('Facturación del Mes', 'S/. ' . number_format($ingresosMes, 2))
                ->description('+12% vs. mes anterior')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([1500, 2400, 3100, 2800, 4200, 5600, (int) $ingresosMes]),

            Stat::make('Servicios Realizados', $mantenimientosTotal)
                ->description('Mantenimientos liquidados')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('info')
                ->chart([3, 7, 5, 9, 12, 10, $mantenimientosTotal]),

            Stat::make('Vehículos Registrados', $vehiculosTotal)
                ->description('Padrón automotor activo')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary')
                ->chart([10, 25, 40, 65, 80, 95, $vehiculosTotal]),
        ];
    }
}
