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
    public string $statusFilter = 'all';
    public string $search = '';

    // Formulario modal de agendar cita (HU03)
    public $vehicle_id_to_book;
    public $appointment_date;
    public $appointment_time;
    public $reason;

    public function mount()
    {
        $client = Auth::user()->client;

        // Si se pasa vehicle_id por URL, lo selecciona directamente
        $urlVehicleId = request()->query('vehicle_id');
        if ($urlVehicleId && $client && $client->vehicles->contains('id', $urlVehicleId)) {
            $this->selectedVehicleId = (int) $urlVehicleId;
        } elseif ($client && $client->vehicles->isNotEmpty()) {
            $this->selectedVehicleId = $client->vehicles->first()->id;
        }

        $this->vehicle_id_to_book = $this->selectedVehicleId;
        $this->appointment_date = now()->format('Y-m-d');
    }

    public function selectVehicle(int $vehicleId)
    {
        $this->selectedVehicleId = $vehicleId;
        $this->vehicle_id_to_book = $vehicleId;
        $this->resetPage();
    }

    public function updatedStatusFilter() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    public function bookAppointment()
    {
        $client = Auth::user()->client;

        $this->validate([
            'vehicle_id_to_book' => ['required', Rule::exists('vehicles', 'id')->where('client_id', $client->id)],
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'reason' => 'required|string|min:5|max:500',
        ]);

        $exists = Appointment::where('appointment_date', $this->appointment_date)
            ->where('appointment_time', $this->appointment_time)
            ->where('status', '!=', 'cancelada')
            ->exists();

        if ($exists) {
            $this->addError('appointment_time', 'Horario ocupado. Por favor selecciona otro turno disponible.');
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

        session()->flash('success', '¡Cita solicitada con éxito! El taller se comunicará para confirmar tu recepción.');
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

        if ($historyQuery) {
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

            $history = $historyQuery->paginate(5);
        } else {
            $history = collect();
        }

        return [
            'vehicles' => $vehicles,
            'selectedVehicle' => $selectedVehicle,
            'history' => $history,
        ];
    }
};
?>

<div class="space-y-6">
    {{-- CABECERA ESTANDARIZADA --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-zinc-200/80 dark:border-zinc-800 pb-5">
        <div>
            <flux:heading size="xl" level="1">Historial y Citas</flux:heading>
            <flux:subheading>Consulta la historia electrónica vehicular y agenda nuevas atenciones técnicas.</flux:subheading>
        </div>

        <flux:modal.trigger name="modal-agendar-cita">
            <flux:button variant="primary" icon="calendar-days">Agendar Nueva Cita</flux:button>
        </flux:modal.trigger>
    </div>

    {{-- ALERTA DE CONFIRMACIÓN --}}
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- SELECTOR DE VEHÍCULO CORREGIDO (ALTO CONTRASTE NUNCA DESAPARECE) --}}
    <div class="space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
            Unidad en Consulta:
        </span>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-none">
            @foreach($vehicles as $veh)
                @php $isSelected = ($selectedVehicleId === $veh->id); @endphp
                <button
                    type="button"
                    wire:click="selectVehicle({{ $veh->id }})"
                    class="group px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all whitespace-nowrap flex items-center gap-2.5 border shadow-2xs select-none {{ $isSelected
                        ? 'bg-zinc-900 text-white border-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 dark:border-zinc-100 ring-2 ring-zinc-900/10 dark:ring-zinc-100/20'
                        : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800/50'
                    }}"
                >
                    {{-- Pastilla de Placa dentro del botón --}}
                    <span class="font-mono font-black text-xs px-2 py-0.5 rounded border tracking-wider {{ $isSelected
                        ? 'bg-zinc-800 text-zinc-100 border-zinc-700 dark:bg-zinc-200 dark:text-zinc-900 dark:border-zinc-300'
                        : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border-zinc-300 dark:border-zinc-700'
                    }}">
                        {{ $veh->placa }}
                    </span>

                    {{-- Marca y Modelo --}}
                    <span class="font-medium">
                        {{ $veh->marca }} {{ $veh->modelo }}
                    </span>

                    {{-- Indicador de selección activa --}}
                    @if($isSelected)
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 dark:bg-emerald-600 animate-pulse"></span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- BARRA DE HERRAMIENTAS: BÚSQUEDA Y FILTROS ESTANDARIZADOS --}}
    <div class="bg-white dark:bg-zinc-900 p-3 sm:p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
        <div class="flex items-center gap-1.5 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg text-xs font-medium self-start sm:self-auto">
            <button
                type="button"
                wire:click="$set('statusFilter', 'all')"
                class="px-3 py-1.5 rounded-md transition-all {{ $statusFilter === 'all'
                    ? 'bg-white dark:bg-zinc-900 font-bold shadow-xs text-zinc-900 dark:text-zinc-100'
                    : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'
                }}"
            >
                Todas
            </button>
            <button
                type="button"
                wire:click="$set('statusFilter', 'finalizada')"
                class="px-3 py-1.5 rounded-md transition-all {{ $statusFilter === 'finalizada'
                    ? 'bg-white dark:bg-zinc-900 font-bold shadow-xs text-emerald-600 dark:text-emerald-400'
                    : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'
                }}"
            >
                Finalizadas
            </button>
            <button
                type="button"
                wire:click="$set('statusFilter', 'pendiente')"
                class="px-3 py-1.5 rounded-md transition-all {{ $statusFilter === 'pendiente'
                    ? 'bg-white dark:bg-zinc-900 font-bold shadow-xs text-amber-600 dark:text-amber-400'
                    : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'
                }}"
            >
                En Proceso
            </button>
        </div>

        <div class="w-full sm:w-72">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por falla o trabajo..."
                icon="magnifying-glass"
                clearable
                size="sm"
            />
        </div>
    </div>

    {{-- LISTA DE ATENCIONES COLAPSABLES --}}
    <div class="space-y-3">
        @forelse($history as $app)
            @php
                $badge = match($app->status) {
                    'confirmada' => 'sky',
                    'finalizada' => 'emerald',
                    'cancelada'  => 'rose',
                    'reprogramada' => 'purple',
                    default      => 'amber',
                };

                $totalCost = 0;
                foreach($app->diagnoses as $d) {
                    if($d->maintenance) $totalCost += (float) $d->maintenance->total_cost;
                }
            @endphp

            <div x-data="{ open: false }" class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden transition-all">
                {{-- Cabecera Compacta --}}
                <div @click="open = !open" class="p-4 sm:p-5 flex items-center justify-between gap-3 cursor-pointer select-none hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/60 dark:border-zinc-700/60 flex flex-col items-center justify-center shrink-0 text-zinc-700 dark:text-zinc-200">
                            <span class="font-black text-xs leading-none">{{ \Carbon\Carbon::parse($app->appointment_date)->format('d') }}</span>
                            <span class="text-[9px] uppercase font-bold text-zinc-400 leading-none mt-0.5">{{ \Carbon\Carbon::parse($app->appointment_date)->translatedFormat('M') }}</span>
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ \Carbon\Carbon::parse($app->appointment_date)->translatedFormat('d \d\e F, Y') }}
                                </span>
                                <flux:badge size="sm" :color="$badge">{{ ucfirst($app->status) }}</flux:badge>
                            </div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-xs sm:max-w-md mt-0.5">
                                {{ $app->reason }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        @if($totalCost > 0)
                            <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                                S/. {{ number_format($totalCost, 2) }}
                            </span>
                        @endif
                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-zinc-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Contenido Desplegable --}}
                <div x-show="open" x-collapse class="border-t border-zinc-100 dark:border-zinc-800 p-4 sm:p-5 bg-zinc-50/50 dark:bg-zinc-950/40 space-y-3.5 text-xs sm:text-sm">
                    <div>
                        <span class="font-bold text-[10px] uppercase tracking-wider text-zinc-400">Motivo del Ingreso / Falla Reportada:</span>
                        <p class="mt-1 text-zinc-800 dark:text-zinc-200 bg-white dark:bg-zinc-900 p-3 rounded-lg border border-zinc-200/80 dark:border-zinc-800">
                            {{ $app->reason }}
                        </p>
                    </div>

                    @foreach($app->diagnoses as $diag)
                        <div class="p-3.5 bg-amber-500/10 border border-amber-500/30 rounded-lg space-y-1 text-amber-950 dark:text-amber-200">
                            <span class="font-bold text-xs">🔍 Diagnóstico Técnico:</span>
                            <p class="text-xs sm:text-sm">{{ $diag->details }}</p>
                        </div>

                        @if($diag->maintenance)
                            <div class="p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg space-y-2">
                                <div class="flex justify-between items-center border-b border-zinc-100 dark:border-zinc-800 pb-2">
                                    <span class="font-bold text-xs uppercase px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                        {{ $diag->maintenance->maintenance_type }}
                                    </span>
                                    <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm">
                                        Liquidación Total: S/. {{ number_format($diag->maintenance->total_cost, 2) }}
                                    </span>
                                </div>
                                <p class="text-zinc-600 dark:text-zinc-400"><strong>Trabajos efectuados:</strong> {{ $diag->maintenance->procedures }}</p>
                                @if($diag->maintenance->observations)
                                    <p class="text-zinc-400 text-xs italic"><strong>Observaciones:</strong> {{ $diag->maintenance->observations }}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-8 text-center text-zinc-500 text-sm border border-zinc-200 dark:border-zinc-800">
                No hay atenciones registradas para este vehículo con el filtro seleccionado.
            </div>
        @endforelse
    </div>

    {{-- Paginador --}}
    @if($history instanceof \Illuminate\Pagination\LengthAwarePaginator && $history->hasPages())
        <div class="pt-2 flex justify-center">
            {{ $history->links() }}
        </div>
    @endif

    {{-- MODAL DE AGENDAR CITA CON FLUX --}}
    <flux:modal name="modal-agendar-cita" class="w-full sm:max-w-lg space-y-5">
        <div>
            <flux:heading size="lg">Agendar Cita en Taller</flux:heading>
            <flux:subheading>Elige el vehículo, fecha disponible y motivo para coordinar la revisión.</flux:subheading>
        </div>

        <form wire:submit="bookAppointment" class="space-y-4">
            <flux:select wire:model="vehicle_id_to_book" label="Vehículo a revisar" required>
                @foreach($vehicles as $veh)
                    <option value="{{ $veh->id }}">{{ $veh->placa }} — {{ $veh->marca }} {{ $veh->modelo }}</option>
                @endforeach
            </flux:select>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <flux:input type="date" wire:model="appointment_date" label="Fecha" min="{{ now()->toDateString() }}" required />
                <flux:select wire:model="appointment_time" label="Turno" required>
                    <option value="">Selecciona hora</option>
                    <option value="08:00">08:00 AM</option>
                    <option value="09:00">09:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:00">11:00 AM</option>
                    <option value="14:00">02:00 PM</option>
                    <option value="15:00">03:00 PM</option>
                    <option value="16:00">04:00 PM</option>
                </flux:select>
            </div>

            <flux:textarea wire:model="reason" label="Falla reportada o servicio" placeholder="Ej: Afinamiento preventivo, vibración en frenos, cambio de aceite..." rows="3" required />

            <div class="flex justify-end gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Confirmar Cita</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
