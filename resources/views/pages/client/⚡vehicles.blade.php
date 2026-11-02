<?php

use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        $client = Auth::user()->client;
        $vehicles = $client ? $client->vehicles()->withCount('appointments')->get() : collect();

        return [
            'client' => $client,
            'vehicles' => $vehicles,
        ];
    }
};
?>

<div class="space-y-6">
    {{-- CABECERA ESTANDARIZADA --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-zinc-200/80 dark:border-zinc-800 pb-5">
        <div>
            <flux:heading size="xl" level="1">Mis Vehículos</flux:heading>
            <flux:subheading>Padrón de unidades registradas bajo tu titularidad en Automotriz Centeno.</flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:badge size="lg" color="zinc">{{ $vehicles->count() }} {{ $vehicles->count() === 1 ? 'Vehículo' : 'Vehículos' }}</flux:badge>
            <flux:button href="{{ route('appointments') }}" variant="primary" icon="calendar-days">
                Agendar Cita
            </flux:button>
        </div>
    </div>

    {{-- CATÁLOGO DE TARJETAS DE VEHÍCULOS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($vehicles as $vehicle)
            <flux:card class="space-y-4 border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex flex-col justify-between">
                <div>
                    {{-- Encabezado de la Tarjeta con Placa --}}
                    <div class="flex justify-between items-center pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="font-mono font-black text-sm tracking-widest bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 px-3 py-1 rounded-md border border-zinc-300 dark:border-zinc-700 shadow-2xs">
                            {{ $vehicle->placa }}
                        </span>
                        <flux:badge size="sm" color="sky">{{ $vehicle->appointments_count }} atenciones</flux:badge>
                    </div>

                    {{-- Especificaciones Técnicas --}}
                    <div class="pt-3 space-y-2">
                        <div class="font-bold text-lg text-zinc-900 dark:text-zinc-100 leading-snug">
                            {{ $vehicle->marca }} {{ $vehicle->modelo }}
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-50/70 dark:bg-zinc-950/40 p-2.5 rounded-lg border border-zinc-100 dark:border-zinc-800">
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-zinc-400">Año</span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $vehicle->anio ?? 'S/A' }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-zinc-400">Color</span>
                                <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $vehicle->color ?? 'Estándar' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pie de Tarjeta con Botón de Navegación --}}
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-end">
                    <flux:button
                        href="{{ route('appointments', ['vehicle_id' => $vehicle->id]) }}"
                        size="sm"
                        variant="subtle"
                        icon-trailing="arrow-right"
                        class="w-full sm:w-auto"
                    >
                        Ver Historial y Atenciones
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <flux:card class="col-span-full text-center py-12 space-y-3 border-dashed border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                <div class="text-4xl">🚗</div>
                <flux:heading size="lg">No tienes vehículos registrados</flux:heading>
                <flux:subheading>Comunícate con recepción en el taller para asociar tus unidades.</flux:subheading>
            </flux:card>
        @endforelse
    </div>
</div>
