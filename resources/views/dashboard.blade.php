<x-layouts::app :title="__('Dashboard')">
    @php
        $client = auth()->user()->client;
        $vehicles = $client ? $client->vehicles : collect();

        // Obtener citas de todos sus vehículos
        $appointments = $client
            ? \App\Models\Appointment::whereIn('vehicle_id', $vehicles->pluck('id'))
                ->with(['diagnoses.maintenance', 'vehicle'])
                ->latest('appointment_date')
                ->get()
            : collect();

        // Cálculo del total acumulado en mantenimientos
        $totalSpent = 0;
        foreach ($appointments as $app) {
            foreach ($app->diagnoses as $diag) {
                if ($diag->maintenance) {
                    $totalSpent += (float) $diag->maintenance->total_cost;
                }
            }
        }

        // Citas próximas / pendientes (Upcoming)
        $upcomingAppointments = $appointments->filter(
            fn ($a) => in_array($a->status, ['pendiente', 'confirmada', 'reprogramada'])
        );

        // Últimas 5 atenciones (Recent transactions)
        $recentServices = $appointments->take(5);
    @endphp

    <div class="space-y-6">
        {{-- CABECERA ESTANDARIZADA --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-zinc-200/80 dark:border-zinc-800 pb-5">
            <div>
                <flux:heading size="xl" level="1">Panel Principal</flux:heading>
                <flux:subheading>Resumen de tu perfil de atenciones</flux:subheading>
            </div>
        </div>

        {{-- 1. HERO BALANCE CARD (Estilo característico de la demo de Flux) --}}
        <div class="relative overflow-hidden rounded-2xl bg-zinc-700 p-6 sm:p-8 text-white shadow-xl border border-zinc-800">
            {{-- Brillo sutil de fondo --}}
            <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-primary-500/15 blur-3xl pointer-events-none"></div>

            <div class="relative flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white/10 text-xs font-bold text-white">
                            AC
                        </span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">
                            Automotriz Centeno • Resumen de Cuenta
                        </span>
                    </div>

                    <div>
                        <div class="text-xs font-medium text-zinc-400">Inversión Total en Servicios</div>
                        <div class="text-3xl sm:text-4xl font-black tracking-tight text-white mt-1">
                            S/. {{ number_format($totalSpent, 2) }}
                        </div>
                    </div>

                    {{-- Indicadores integrados en el Hero --}}
                    <div class="flex flex-wrap items-center gap-3 pt-1 text-xs text-zinc-300">
                        <div class="flex items-center gap-1.5 bg-white/5 px-2.5 py-1 rounded-md border border-white/10">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span>{{ $vehicles->count() }} {{ $vehicles->count() === 1 ? 'unidad registrada' : 'unidades registradas' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-white/5 px-2.5 py-1 rounded-md border border-white/10">
                            <span>{{ $appointments->count() }} visitas históricas</span>
                        </div>
                        @if($upcoming = $upcomingAppointments->first())
                            <div class="flex items-center gap-1.5 bg-primary-500/20 text-primary-200 px-2.5 py-1 rounded-md border border-primary-500/30">
                                <span>Próximo turno: {{ \Carbon\Carbon::parse($upcoming->appointment_date)->format('d/m/Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Botones de Acción integrados --}}
                <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                    <flux:button
                        href="{{ route('appointments') }}"
                        variant="primary"
                        icon="calendar-days"
                        class="w-full sm:w-auto shadow-sm"
                    >
                        Agendar Cita
                    </flux:button>

                    <flux:button
                        href="{{ route('vehicles') }}"
                        variant="subtle"
                        icon="truck"
                        class="w-full sm:w-auto !bg-white/10 hover:!bg-white/20 !text-white border-0"
                    >
                        Mis Unidades
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- 2. DISTRIBUCIÓN EN 2 COLUMNAS (Estilo Demo Flux: 2/3 y 1/3) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- COLUMNA IZQUIERDA (2/3): SERVICIOS RECIENTES (PAST TRANSACTIONS) --}}
            <div class="lg:col-span-2 space-y-4">
                <flux:card class="p-0 overflow-hidden border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs">
                    {{-- Encabezado de la lista --}}
                    <div class="p-5 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <div>
                            <flux:heading size="lg">Atenciones Recientes</flux:heading>
                            <flux:subheading>Últimos diagnósticos y mantenimientos realizados</flux:subheading>
                        </div>

                        <flux:button href="{{ route('appointments') }}" size="sm" variant="subtle" icon-trailing="arrow-right">
                            Ver historial completo
                        </flux:button>
                    </div>

                    {{-- Lista de Atenciones con estilo de transacciones --}}
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($recentServices as $app)
                            @php
                                $diag = $app->diagnoses->first();
                                $maint = $diag?->maintenance;
                                $cost = $maint ? (float) $maint->total_cost : 0;
                            @endphp

                            <div class="p-4 sm:p-5 flex items-center justify-between gap-4 hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    {{-- Ícono estilo avatar de servicio --}}
                                    <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 flex items-center justify-center shrink-0 text-base border border-zinc-200/60 dark:border-zinc-700/60">
                                        🔧
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100 truncate">
                                                {{ $app->vehicle?->marca }} {{ $app->vehicle?->modelo }}
                                            </span>
                                            <span class="font-mono text-[11px] font-black px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                                                {{ $app->vehicle?->placa }}
                                            </span>
                                        </div>

                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate mt-0.5">
                                            {{ $maint?->procedures ?? $app->reason }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Fecha, Badge y Monto --}}
                                <div class="text-end shrink-0">
                                    @if($cost > 0)
                                        <div class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                                            S/. {{ number_format($cost, 2) }}
                                        </div>
                                    @else
                                        <div class="text-xs font-semibold text-zinc-400 dark:text-zinc-500">Por liquidar</div>
                                    @endif

                                    <div class="flex items-center justify-end gap-1.5 mt-1">
                                        <span class="text-[11px] text-zinc-400 dark:text-zinc-500">
                                            {{ \Carbon\Carbon::parse($app->appointment_date)->format('d/m/Y') }}
                                        </span>
                                        <flux:badge size="sm" :color="match($app->status) {
                                            'confirmada' => 'sky',
                                            'finalizada' => 'emerald',
                                            'cancelada'  => 'rose',
                                            'reprogramada' => 'purple',
                                            default      => 'amber',
                                        }">
                                            {{ ucfirst($app->status) }}
                                        </flux:badge>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-zinc-500 text-sm">
                                No se registran atenciones recientes.
                            </div>
                        @endforelse
                    </div>
                </flux:card>
            </div>

            {{-- COLUMNA DERECHA (1/3): PRÓXIMAS CITAS Y RESUMEN DE VEHÍCULOS --}}
            <div class="space-y-6">

                {{-- TARJETA: PRÓXIMAS CITAS (Upcoming) --}}
                <flux:card class="space-y-4 border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs">
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg">Próximos Turnos</flux:heading>
                        <flux:badge size="sm" color="zinc">{{ $upcomingAppointments->count() }}</flux:badge>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($upcomingAppointments->take(3) as $upcoming)
                            <div class="p-3.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-950/40 space-y-1.5">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <div class="font-bold text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ \Carbon\Carbon::parse($upcoming->appointment_date)->translatedFormat('d \d\e F') }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            Turno: {{ \Carbon\Carbon::parse($upcoming->appointment_time)->format('h:i A') }}
                                        </div>
                                    </div>
                                    <flux:badge size="sm" :color="$upcoming->status === 'confirmada' ? 'sky' : 'amber'">
                                        {{ ucfirst($upcoming->status) }}
                                    </flux:badge>
                                </div>

                                <div class="text-xs font-mono font-bold text-zinc-700 dark:text-zinc-300">
                                    {{ $upcoming->vehicle?->placa }} — {{ $upcoming->vehicle?->marca }} {{ $upcoming->vehicle?->modelo }}
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs text-zinc-500 space-y-2">
                                <div class="text-2xl">📅</div>
                                <p>No tienes citas pendientes por atender.</p>
                                <flux:button href="{{ route('appointments') }}" size="xs" variant="primary">Agendar Turno</flux:button>
                            </div>
                        @endforelse
                    </div>
                </flux:card>

                {{-- TARJETA: MIS UNIDADES RÁPIDAS --}}
                <flux:card class="space-y-3 border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs">
                    <div class="flex items-center justify-between">
                        <flux:heading size="base">Mis Unidades</flux:heading>
                        <flux:button href="{{ route('vehicles') }}" size="xs" variant="ghost">Ver catálogo</flux:button>
                    </div>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($vehicles->take(3) as $veh)
                            <div class="py-2.5 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-bold text-zinc-800 dark:text-zinc-200">
                                        {{ $veh->marca }} {{ $veh->modelo }}
                                    </div>
                                    <div class="text-zinc-400 dark:text-zinc-500">Año {{ $veh->anio ?? 'S/A' }}</div>
                                </div>
                                <span class="font-mono font-black px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                                    {{ $veh->placa }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </flux:card>

            </div>
        </div>

    </div>
</x-layouts::app>
