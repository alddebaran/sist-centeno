<?php

use App\Models\Vehicle;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public ?int $selectedVehicleId = null;
    public $appointment_date;
    public $appointment_time;
    public $reason;
    public $vehicle_id_to_book;

    public function mount()
    {
        $client = Auth::user()->client;

        if ($client && $client->vehicles->isNotEmpty()) {
            $this->selectedVehicleId = $client->vehicles->first()->id;
            $this->vehicle_id_to_book = $this->selectedVehicleId;
        }
    }

    public function selectVehicle(int $vehicleId)
    {
        $this->selectedVehicleId = $vehicleId;
        $this->vehicle_id_to_book = $vehicleId;
    }

    public function bookAppointment()
    {
        $this->validate([
            'vehicle_id_to_book' => 'required|exists:vehicles,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'reason' => 'required|min:5',
        ]);

        $client = Auth::user()->client;

        Appointment::create([
            'client_id' => $client->id,
            'vehicle_id' => $this->vehicle_id_to_book,
            'appointment_date' => $this->appointment_date,
            'appointment_time' => $this->appointment_time,
            'reason' => $this->reason,
            'status' => 'pendiente',
        ]);

        $this->reset(['appointment_date', 'appointment_time', 'reason']);
        session()->flash('success', '¡Cita registrada con éxito! El taller la confirmará a la brevedad.');
    }

    public function with(): array
    {
        $client = Auth::user()->client;
        $vehicles = $client ? $client->vehicles : collect();
        $selectedVehicle = $vehicles->firstWhere('id', $this->selectedVehicleId);

        $history = $selectedVehicle
            ? $selectedVehicle->appointments()
                ->with(['diagnoses.maintenance'])
                ->latest('appointment_date')
                ->get()
            : collect();

        return [
            'client' => $client,
            'vehicles' => $vehicles,
            'selectedVehicle' => $selectedVehicle,
            'history' => $history,
        ];
    }
};
?>

{{-- AQUÍ ABAJO PEGAS DIRECTAMENTE TU DISEÑO DE FLUX/BLADE --}}
<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 space-y-8">
    <div class="flex justify-between items-center border-b pb-5">
        <div>
            <flux:heading size="xl">Portal del Propietario</flux:heading>
            <flux:subheading>Bienvenido(a), {{ auth()->user()->name }}</flux:subheading>
        </div>
        <flux:modal.trigger name="modal-agendar-cita">
            <flux:button variant="primary" icon="calendar-days">Agendar Nueva Cita</flux:button>
        </flux:modal.trigger>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Selector de Vehículos --}}
    <div>
        <flux:heading size="lg" class="mb-3">Mis Vehículos</flux:heading>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @forelse($vehicles as $vehicle)
                <div
                    wire:click="selectVehicle({{ $vehicle->id }})"
                    class="cursor-pointer p-4 rounded-xl border {{ $selectedVehicleId === $vehicle->id ? 'border-primary-500 ring-2 ring-primary-500' : 'border-zinc-200' }}"
                >
                    <span class="font-mono font-bold bg-zinc-100 px-2 py-0.5 rounded">{{ $vehicle->placa }}</span>
                    <div class="mt-2 font-medium">{{ $vehicle->marca }} {{ $vehicle->modelo }} ({{ $vehicle->anio }})</div>
                </div>
            @empty
                <p class="text-zinc-500">No tienes vehículos registrados.</p>
            @endforelse
        </div>
    </div>

    {{-- Historial de Trabajos --}}
    @if($selectedVehicle)
        <flux:heading size="lg">Historial Técnico: {{ $selectedVehicle->placa }}</flux:heading>
        <div class="space-y-4">
            @forelse($history as $app)
                <flux:card class="space-y-2">
                    <div class="flex justify-between font-bold">
                        <span>Atención: {{ \Carbon\Carbon::parse($app->appointment_date)->format('d/m/Y') }}</span>
                        <flux:badge size="sm">{{ ucfirst($app->status) }}</flux:badge>
                    </div>
                    <p class="text-sm"><strong>Motivo:</strong> {{ $app->reason }}</p>

                    @foreach($app->diagnoses as $diag)
                        <div class="pl-3 border-l-2 border-amber-500 text-sm">
                            <p class="text-amber-700"><strong>Diagnóstico:</strong> {{ $diag->details }}</p>
                            @if($diag->maintenance)
                                <p class="text-emerald-700"><strong>Trabajos:</strong> {{ $diag->maintenance->procedures }} (Total: S/. {{ number_format($diag->maintenance->total_cost, 2) }})</p>
                            @endif
                        </div>
                    @endforeach
                </flux:card>
            @empty
                <p class="text-zinc-500">No hay atenciones previas para este vehículo.</p>
            @endforelse
        </div>
    @endif
</div>
