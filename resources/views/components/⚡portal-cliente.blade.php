<?php

use App\Models\Vehicle;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public ?int $selectedVehicleId = null;

    // Filtros de búsqueda para evitar scroll infinito (Mejores prácticas de UX)
    public string $statusFilter = 'all'; // all, finalizada, pendiente
    public string $search = '';

    // Campos para agendar cita (HU03)
    public $vehicle_id_to_book;
    public $appointment_date;
    public $appointment_time;
    public $reason;

    public function mount()
    {
        $client = Auth::user()->client;

        if ($client && $client->vehicles->isNotEmpty()) {
            $this->selectedVehicleId = $client->vehicles->first()->id;
            $this->vehicle_id_to_book = $this->selectedVehicleId;
        }

        $this->appointment_date = now()->format('Y-m-d');
    }

    public function selectVehicle(int $vehicleId)
    {
        $this->selectedVehicleId = $vehicleId;
        $this->vehicle_id_to_book = $vehicleId;
        $this->resetPage(); // Reinicia la paginación al cambiar de auto
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function bookAppointment()
    {
        $client = Auth::user()->client;

        if (! $client) {
            session()->flash('error', 'No tienes un perfil de cliente asociado.');
            return;
        }

        $this->validate([
            'vehicle_id_to_book' => [
                'required',
                Rule::exists('vehicles', 'id')->where('client_id', $client->id),
            ],
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'reason' => 'required|string|min:5|max:500',
        ], [
            'vehicle_id_to_book.required' => 'Debes seleccionar uno de tus vehículos.',
            'appointment_date.after_or_equal' => 'La fecha no puede ser anterior al día de hoy.',
            'appointment_time.required' => 'Selecciona un horario disponible.',
            'reason.required' => 'Por favor indica el motivo de la revisión.',
            'reason.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $exists = Appointment::where('appointment_date', $this->appointment_date)
            ->where('appointment_time', $this->appointment_time)
            ->where('status', '!=', 'cancelada')
            ->exists();

        if ($exists) {
            $this->addError('appointment_time', 'Este horario ya está ocupado en el taller. Por favor selecciona otra hora.');
            return;
        }

        Appointment::create([
            'client_id' => $client->id,
            'vehicle_id' => $this->vehicle_id_to_book,
            'appointment_date' => $this->appointment_date,
            'appointment_time' => $this->appointment_time,
            'reason' => $this->reason,
            'status' => 'pendiente',
        ]);

        $this->reset(['reason', 'appointment_time']);

        if (class_exists(\Flux\Flux::class)) {
            \Flux\Flux::modal('modal-agendar-cita')->close();
        }

        session()->flash('success', '¡Cita solicitada con éxito! El taller se comunicará para confirmar la recepción.');
    }

    public function with(): array
    {
        $client = Auth::user()->client;
        $vehicles = $client ? $client->vehicles : collect();
        $selectedVehicle = $vehicles->firstWhere('id', $this->selectedVehicleId);

        $historyQuery = $selectedVehicle
            ? $selectedVehicle->appointments()
                ->with(['diagnoses.maintenance', 'diagnoses.technician'])
                ->latest('appointment_date')
            : null;

        // Estadísticas rápidas (KPIs)
        $totalSpent = 0;
        $nextAppointment = null;
        $totalServicesCount = 0;

        if ($historyQuery) {
            $allAppointments = (clone $historyQuery)->get();
            $totalServicesCount = $allAppointments->count();

            // Próxima cita activa
            $nextAppointment = $allAppointments->first(fn ($a) => in_array($a->status, ['pendiente', 'confirmada', 'reprogramada']));

            // Total gastado en mantenimientos
            foreach ($allAppointments as $app) {
                foreach ($app->diagnoses as $diag) {
                    if ($diag->maintenance) {
                        $totalSpent += (float) $diag->maintenance->total_cost;
                    }
                }
            }

            // Aplicar filtros de búsqueda
            if ($this->statusFilter === 'finalizada') {
                $historyQuery->where('status', 'finalizada');
            } elseif ($this->statusFilter === 'pendiente') {
                $historyQuery->whereIn('status', ['pendiente', 'confirmada', 'reprogramada']);
            }

            if (! empty($this->search)) {
                $term = '%' . $this->search . '%';
                $historyQuery->where(function ($q) use ($term) {
                    $q->where('reason', 'like', $term)
                        ->orWhereHas('diagnoses', fn ($d) => $d->where('details', 'like', $term));
                });
            }

            $paginatedHistory = $historyQuery->paginate(4); // 4 atenciones por página
        } else {
            $paginatedHistory = collect();
        }

        return [
            'client' => $client,
            'vehicles' => $vehicles,
            'selectedVehicle' => $selectedVehicle,
            'history' => $paginatedHistory,
            'kpis' => [
                'totalSpent' => $totalSpent,
                'totalCount' => $totalServicesCount,
                'nextAppointment' => $nextAppointment,
            ],
        ];
    }
};
?>

<div class="min-h-screen bg-zinc-50/70 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 pb-24 md:pb-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10 space-y-6 sm:space-y-8">

        {{-- 1. HEADER CON SALUDO Y CTA --}}
        <header class="bg-white dark:bg-zinc-900 rounded-2xl p-5 sm:p-6 border border-zinc-200/80 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="space-y-1">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight">
                    Hola, {{ explode(' ', trim(auth()->user()->name))[0] }}
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500">
                    Historial electrónico y seguimiento técnico de tus vehículos.
                </p>
            </div>

            <flux:modal.trigger name="modal-agendar-cita">
                <flux:button variant="primary" icon="calendar-days" class="w-full sm:w-auto shadow-sm">
                    Agendar Cita
                </flux:button>
            </flux:modal.trigger>
        </header>

        {{-- ALERTAS DE ACCIÓN --}}
        @if (session()->has('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- 2. SELECTOR DE VEHÍCULOS (Horizontal Scroll en móvil) --}}
        <section class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-xs font-bold tracking-wider uppercase text-zinc-400">
                    Mis Unidades ({{ $vehicles->count() }})
                </h2>
                <span class="text-xs text-zinc-400 sm:hidden">Desliza para elegir</span>
            </div>

            <div class="flex sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-3 overflow-x-auto pb-2 sm:pb-0 snap-x snap-mandatory scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
                @forelse($vehicles as $vehicle)
                    <div
                        wire:click="selectVehicle({{ $vehicle->id }})"
                        class="min-w-[240px] sm:min-w-0 flex-1 snap-start cursor-pointer rounded-xl p-4 transition-all duration-200 select-none border {{ $selectedVehicleId === $vehicle->id ? 'bg-white dark:bg-zinc-900 border-primary-500 shadow-md ring-2 ring-primary-500/20' : 'bg-white/70 dark:bg-zinc-900/50 border-zinc-200 dark:border-zinc-800 hover:border-zinc-300' }}"
                    >
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-mono font-black text-sm tracking-widest bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 px-2.5 py-1 rounded border border-zinc-300 dark:border-zinc-700">
                                {{ $vehicle->placa }}
                            </span>
                            @if($selectedVehicleId === $vehicle->id)
                                <span class="text-[11px] font-bold text-primary-600 dark:text-primary-400 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span> Activo
                                </span>
                            @endif
                        </div>

                        <div class="font-semibold text-zinc-900 dark:text-zinc-100 text-base">
                            {{ $vehicle->marca }} {{ $vehicle->modelo }}
                        </div>
                        <div class="text-xs text-zinc-500 mt-1">
                            Año {{ $vehicle->anio ?? 'S/A' }} • Color {{ $vehicle->color ?? 'Estándar' }}
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-zinc-900 rounded-2xl p-6 text-center border border-dashed border-zinc-300 dark:border-zinc-800 text-zinc-500 text-sm">
                        No registras vehículos asociados a tu cuenta.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- 3. TARJETAS DE RESUMEN EJECUTIVO (KPIS DEL VEHÍCULO SELECCIONADO) --}}
        @if($selectedVehicle)
            <section class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                {{-- Mantenimientos Totales --}}
                <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center text-lg">
                        🔧
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 font-medium">Historial de Visitas</div>
                        <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $kpis['totalCount'] }} atenciones</div>
                    </div>
                </div>

                {{-- Inversión en Mantenimiento --}}
                <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center text-lg">
                        💰
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 font-medium">Inversión en Mantenimiento</div>
                        <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            S/. {{ number_format($kpis['totalSpent'], 2) }}
                        </div>
                    </div>
                </div>

                {{-- Próxima Cita --}}
                <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center text-lg">
                        📅
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 font-medium">Próximo Turno</div>
                        @if($kpis['nextAppointment'])
                            <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                {{ \Carbon\Carbon::parse($kpis['nextAppointment']->appointment_date)->format('d/m/Y') }} ({{ ucfirst($kpis['nextAppointment']->status) }})
                            </div>
                        @else
                            <div class="text-sm font-medium text-zinc-400">Sin citas pendientes</div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- 4. HISTORIA VEHICULAR CON FILTROS Y TARJETAS COLAPSABLES --}}
            <section class="space-y-4">
                {{-- Barra de Herramientas: Búsqueda y Chips de Estado --}}
                <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                    {{-- Chips de Filtro --}}
                    <div class="flex items-center gap-1.5 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl self-start sm:self-auto text-xs font-medium">
                        <button
                            type="button"
                            wire:click="$set('statusFilter', 'all')"
                            class="px-3 py-1.5 rounded-lg transition-all {{ $statusFilter === 'all' ? 'bg-white dark:bg-zinc-900 shadow-xs text-zinc-900 dark:text-zinc-100 font-bold' : 'text-zinc-500 hover:text-zinc-800' }}"
                        >
                            Todas
                        </button>
                        <button
                            type="button"
                            wire:click="$set('statusFilter', 'finalizada')"
                            class="px-3 py-1.5 rounded-lg transition-all {{ $statusFilter === 'finalizada' ? 'bg-white dark:bg-zinc-900 shadow-xs text-emerald-600 font-bold' : 'text-zinc-500 hover:text-zinc-800' }}"
                        >
                            Finalizadas
                        </button>
                        <button
                            type="button"
                            wire:click="$set('statusFilter', 'pendiente')"
                            class="px-3 py-1.5 rounded-lg transition-all {{ $statusFilter === 'pendiente' ? 'bg-white dark:bg-zinc-900 shadow-xs text-amber-600 font-bold' : 'text-zinc-500 hover:text-zinc-800' }}"
                        >
                            En Proceso / Citas
                        </button>
                    </div>

                    {{-- Buscador reactivo rápido --}}
                    <div class="relative w-full sm:w-64">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Buscar falla o trabajo..."
                            class="w-full text-xs bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        />
                        @if($search)
                            <button wire:click="$set('search', '')" class="absolute right-2.5 top-2 text-xs text-zinc-400 hover:text-zinc-600">✕</button>
                        @endif
                    </div>
                </div>

                {{-- Listado de Atenciones Colapsables (Progressive Disclosure) --}}
                <div class="space-y-3">
                    @forelse($history as $app)
                        @php
                            $statusBadge = match($app->status) {
                                'confirmada' => 'sky',
                                'finalizada' => 'emerald',
                                'cancelada'  => 'rose',
                                'reprogramada' => 'purple',
                                default      => 'amber',
                            };

                            // Calcular monto de esta cita específica si tiene mantenimiento
                            $costoCita = 0;
                            foreach($app->diagnoses as $d) {
                                if($d->maintenance) $costoCita += (float) $d->maintenance->total_cost;
                            }
                        @endphp

                        {{-- Componente Colapsable con Alpine.js (Cero latencia de red al abrir/cerrar) --}}
                        <div
                            x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }"
                            class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden transition-all"
                        >
                            {{-- Cabecera Compacta (Clickable para expandir/contraer) --}}
                            <div
                                @click="open = !open"
                                class="p-4 sm:p-5 flex items-center justify-between gap-3 cursor-pointer hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 select-none"
                            >
                                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center shrink-0 font-bold text-zinc-600 dark:text-zinc-300 text-sm">
                                        {{ \Carbon\Carbon::parse($app->appointment_date)->format('d') }}
                                        <span class="text-[9px] uppercase block text-center font-normal">{{ \Carbon\Carbon::parse($app->appointment_date)->translatedFormat('M') }}</span>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ \Carbon\Carbon::parse($app->appointment_date)->translatedFormat('d \d\e F, Y') }}
                                            </span>
                                            <flux:badge size="sm" :color="$statusBadge">
                                                {{ ucfirst($app->status) }}
                                            </flux:badge>
                                        </div>
                                        <p class="text-xs text-zinc-500 truncate mt-0.5 max-w-xs sm:max-w-md">
                                            {{ $app->reason }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    @if($costoCita > 0)
                                        <span class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400">
                                            S/. {{ number_format($costoCita, 2) }}
                                        </span>
                                    @endif

                                    {{-- Flecha rotatoria acordeón --}}
                                    <svg
                                        :class="open ? 'rotate-180' : ''"
                                        class="w-4 h-4 text-zinc-400 transition-transform duration-200"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Detalle Expandible (Solo visible al hacer clic) --}}
                            <div
                                x-show="open"
                                x-collapse
                                class="border-t border-zinc-100 dark:border-zinc-800 p-4 sm:p-5 bg-zinc-50/50 dark:bg-zinc-950/40 space-y-4"
                            >
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Detalle de la Solicitud / Falla:</span>
                                    <p class="text-xs sm:text-sm text-zinc-700 dark:text-zinc-300 mt-1 bg-white dark:bg-zinc-900 p-3 rounded-lg border border-zinc-200/80 dark:border-zinc-800">
                                        {{ $app->reason }}
                                    </p>
                                </div>

                                {{-- Diagnósticos y Mantenimientos --}}
                                @if($app->diagnoses->isNotEmpty())
                                    <div class="space-y-3">
                                        @foreach($app->diagnoses as $diag)
                                            <div class="p-3.5 rounded-lg bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/40 text-xs sm:text-sm space-y-1">
                                                <div class="flex items-center gap-2 font-semibold text-amber-800 dark:text-amber-300">
                                                    <span>🔍 Diagnóstico Técnico</span>
                                                    @if($diag->technician)
                                                        <span class="text-xs font-normal text-amber-600 dark:text-amber-400">• Evaluado por: {{ $diag->technician->name }}</span>
                                                    @endif
                                                </div>
                                                <p class="text-zinc-700 dark:text-zinc-300">
                                                    {{ $diag->details }}
                                                </p>
                                            </div>

                                            @if($diag->maintenance)
                                                <div class="p-4 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-2 text-xs sm:text-sm">
                                                    <div class="flex justify-between items-center border-b border-zinc-100 dark:border-zinc-800 pb-2">
                                                        <span class="font-bold text-xs uppercase px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                                            {{ $diag->maintenance->maintenance_type }}
                                                        </span>
                                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                                            Liquidación: S/. {{ number_format($diag->maintenance->total_cost, 2) }}
                                                        </span>
                                                    </div>
                                                    <p class="text-zinc-600 dark:text-zinc-400"><strong>Trabajos efectuados:</strong> {{ $diag->maintenance->procedures }}</p>
                                                    @if($diag->maintenance->observations)
                                                        <p class="text-zinc-500 italic"><strong>Obs:</strong> {{ $diag->maintenance->observations }}</p>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-xs text-zinc-400 italic text-center py-2">
                                        Esta cita se encuentra en programación inicial. El informe técnico se cargará al ingresar a taller.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-8 text-center border border-zinc-200 dark:border-zinc-800 text-zinc-500 text-sm">
                            No se encontraron atenciones que coincidan con el filtro seleccionado.
                        </div>
                    @endforelse
                </div>

                {{-- Paginador de Livewire (Evita que la página crezca infinitamente) --}}
                @if($history instanceof \Illuminate\Pagination\LengthAwarePaginator && $history->hasPages())
                    <div class="pt-2 flex justify-center">
                        {{ $history->links() }}
                    </div>
                @endif
            </section>
        @endif

    </div>

    {{-- 5. BOTÓN FLOTANTE EN MÓVIL (FAB) --}}
    <div class="sm:hidden fixed bottom-5 right-5 z-40">
        <flux:modal.trigger name="modal-agendar-cita">
            <button type="button" class="flex items-center gap-2 bg-primary-600 text-white font-semibold py-3.5 px-5 rounded-full shadow-lg active:scale-95 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Cita</span>
            </button>
        </flux:modal.trigger>
    </div>

    {{-- 6. MODAL INTERACTIVO DE AGENDAR CITA (HU03) --}}
    <flux:modal name="modal-agendar-cita" class="w-full sm:max-w-lg space-y-5">
        <div>
            <flux:heading size="lg">Agendar Cita en Taller</flux:heading>
            <flux:subheading>Elige tu vehículo, turno de atención y describe el motivo de ingreso.</flux:subheading>
        </div>

        <form wire:submit="bookAppointment" class="space-y-4">
            <div>
                <flux:select wire:model="vehicle_id_to_book" label="Vehículo a ingresar" required>
                    @foreach($vehicles as $veh)
                        <option value="{{ $veh->id }}">{{ $veh->placa }} — {{ $veh->marca }} {{ $veh->modelo }}</option>
                    @endforeach
                </flux:select>
                @error('vehicle_id_to_book') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <flux:input
                        type="date"
                        wire:model="appointment_date"
                        label="Fecha de Cita"
                        min="{{ now()->toDateString() }}"
                        required
                    />
                    @error('appointment_date') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:select wire:model="appointment_time" label="Turno / Hora" required>
                        <option value="">Selecciona hora</option>
                        <option value="08:00">08:00 AM</option>
                        <option value="08:30">08:30 AM</option>
                        <option value="09:00">09:00 AM</option>
                        <option value="09:30">09:30 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="10:30">10:30 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="11:30">11:30 AM</option>
                        <option value="14:00">02:00 PM</option>
                        <option value="14:30">02:30 PM</option>
                        <option value="15:00">03:00 PM</option>
                        <option value="15:30">03:30 PM</option>
                        <option value="16:00">04:00 PM</option>
                        <option value="16:30">04:30 PM</option>
                        <option value="17:00">05:00 PM</option>
                    </flux:select>
                    @error('appointment_time') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <flux:textarea
                    wire:model="reason"
                    label="Falla reportada / Servicio requerido"
                    placeholder="Ej: Afinamiento preventivo, vibración al frenar, cambio de aceite 5W-30..."
                    rows="3"
                    required
                />
                @error('reason') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Confirmar Cita</span>
                    <span wire:loading>Registrando...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
