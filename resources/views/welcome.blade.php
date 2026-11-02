<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Automotriz Centeno | Gestión de Servicios e Historia Electrónica Vehicular</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Styles / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 font-sans antialiased selection:bg-primary-600 selection:text-white">

{{-- 1. NAVBAR DE NAVEGACIÓN --}}
<header class="sticky top-0 z-50 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border-b border-zinc-200/80 dark:border-zinc-800 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
        {{-- Logo y Marca --}}
        <a href="#inicio" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 flex items-center justify-center font-black text-base shadow-sm group-hover:scale-105 transition-transform">
                AC
            </div>
            <div>
                <div class="font-extrabold text-base sm:text-lg tracking-tight leading-none text-zinc-900 dark:text-white">
                    Automotriz Centeno
                </div>
                <span class="text-[10px] sm:text-xs text-zinc-500 font-medium">CYC SERVICE SAC • Taller Especializado</span>
            </div>
        </a>

        {{-- Menú de Enlaces Desktop --}}
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-zinc-600 dark:text-zinc-300">
            <a href="#servicios" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Servicios</a>
            <a href="#hev" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Historia Vehicular</a>
            <a href="#proceso" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">¿Cómo Funciona?</a>
            <a href="#contacto" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Contacto</a>
        </nav>

        {{-- Acceso a Cuenta / CTA --}}
        <div class="flex items-center gap-3">
            @auth
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-semibold shadow-xs transition-transform active:scale-95"
                >
                    <span>Mi Panel</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    class="px-3.5 py-2 text-xs sm:text-sm font-semibold text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white transition-colors"
                >
                    Ingresar
                </a>

                @if (Route::has('register'))
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center px-4 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-100 text-white dark:text-zinc-900 text-xs sm:text-sm font-semibold shadow-sm transition-transform active:scale-95"
                    >
                        Registrarse
                    </a>
                @endif
            @endauth
        </div>
    </div>
</header>

{{-- 2. HERO SECTION --}}
<section id="inicio" class="relative overflow-hidden pt-12 sm:pt-20 pb-16 sm:pb-28">
    {{-- Brillo ambiental de fondo --}}
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[350px] bg-primary-500/10 dark:bg-primary-500/5 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-3xl mx-auto space-y-6">

            {{-- Titular Principal --}}
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-zinc-900 dark:text-white leading-[1.15]">
                Cuidado experto y <span class="bg-gradient-to-r from-primary-600 to-amber-500 bg-clip-text">trazabilidad digital</span> para tu vehículo.
            </h1>

            {{-- Subtítulo --}}
            <p class="text-sm sm:text-lg text-zinc-600 dark:text-zinc-400 leading-relaxed font-normal max-w-2xl mx-auto">
                Agenda tus atenciones de mantenimiento en tiempo real y consulta tu <strong>Historia Electrónica Vehicular (HEV)</strong>: diagnósticos, repuestos y costos con total transparencia.
            </p>

            {{-- Botones de Llamado a la Acción (CTA) --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a
                    href="{{ route('login') }}"
                    class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 text-zinc-800 dark:text-zinc-200 font-bold text-sm shadow-2xs transition-all active:scale-95 flex items-center justify-center gap-2"
                >
                    <span>Agendar Cita en Línea</span>
                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                </a>

            </div>

            {{-- Indicadores Clave en Barra Rápida --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-8 border-t border-zinc-200/80 dark:border-zinc-800/80 text-center">
                <div class="space-y-0.5">
                    <div class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white">100%</div>
                    <div class="text-xs text-zinc-500 font-medium">Historial Digitalizado</div>
                </div>
                <div class="space-y-0.5">
                    <div class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white">24/7</div>
                    <div class="text-xs text-zinc-500 font-medium">Reserva de Citas</div>
                </div>
                <div class="space-y-0.5">
                    <div class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white">0</div>
                    <div class="text-xs text-zinc-500 font-medium">Cobros Sorpresa</div>
                </div>
                <div class="space-y-0.5">
                    <div class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white">OBD-II</div>
                    <div class="text-xs text-zinc-500 font-medium">Diagnóstico Escaneado</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 3. PROPUESTA DE VALOR / BENEFICIOS DEL SISTEMA --}}
<section id="hev" class="py-16 bg-white dark:bg-zinc-900 border-y border-zinc-200 dark:border-zinc-800 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400">Innovación en Taller</span>
            <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">El respaldo que tu vehículo necesita</h2>
            <p class="text-sm sm:text-base text-zinc-500 dark:text-zinc-400">
                Se acabó perder boletas, olvidar el kilometraje del último cambio de aceite o dudar sobre qué reparaciones se hicieron.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Tarjeta 1: HEV --}}
            <div class="p-6 rounded-2xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200/80 dark:border-zinc-800/80 space-y-4 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xl font-bold">
                    📄
                </div>
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Historia Electrónica Vehicular (HEV)</h3>
                <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Hoja de vida digitalizada de tu unidad. Cada informe técnico, código de falla, repuesto instalado y kilometraje queda archivado permanentemente.
                </p>
            </div>

            {{-- Tarjeta 2: Cero Colas y Agenda --}}
            <div class="p-6 rounded-2xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200/80 dark:border-zinc-800/80 space-y-4 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold">
                    ⏱️
                </div>
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Agenda Centralizada en Vivo</h3>
                <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Reserva tu horario desde tu celular. El taller prepara el box de atención antes de que llegues, eliminando demoras innecesarias.
                </p>
            </div>

            {{-- Tarjeta 3: Transparencia en Costos --}}
            <div class="p-6 rounded-2xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200/80 dark:border-zinc-800/80 space-y-4 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold">
                    💰
                </div>
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Liquidación y Presupuesto Claro</h3>
                <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Desglose de mano de obra y repuestos en soles (S/.). Conoce el estado exacto de tu servicio desde el diagnóstico inicial hasta la entrega.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- 4. SERVICIOS DEL TALLER --}}
<section id="servicios" class="py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400">Nuestras Especialidades</span>
            <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">Servicios Técnicos Especializados</h2>
            <p class="text-sm sm:text-base text-zinc-500">Mantenimiento automotriz multimarca con estándares de concesionario.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Servicio 1 --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-3 shadow-xs">
                <span class="text-2xl">🛢️</span>
                <h4 class="font-bold text-base text-zinc-900 dark:text-zinc-100">Mantenimiento Preventivo</h4>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    Cambio de aceite multigrado/sintético, cambio de filtros (aire, aceite, cabina), rotación de neumáticos y revisión de 25 puntos de seguridad.
                </p>
            </div>

            {{-- Servicio 2 --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-3 shadow-xs">
                <span class="text-2xl">🛑</span>
                <h4 class="font-bold text-base text-zinc-900 dark:text-zinc-100">Frenos y Suspensión</h4>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    Rectificación de discos y tambores, cambio de pastillas de cerámica, purgado de líquido DOT4 y diagnóstico de amortiguadores y trapecios.
                </p>
            </div>

            {{-- Servicio 3 --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-3 shadow-xs">
                <span class="text-2xl">💻</span>
                <h4 class="font-bold text-base text-zinc-900 dark:text-zinc-100">Escaneo y Diagnóstico OBD-II</h4>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    Detección de códigos de falla (Check Engine, ABS, Airbag), análisis de flujo de sensores en vivo y reseteo de intervalos de servicio.
                </p>
            </div>

            {{-- Servicio 4 --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-3 shadow-xs">
                <span class="text-2xl">⚡</span>
                <h4 class="font-bold text-base text-zinc-900 dark:text-zinc-100">Afinamiento Electrónico</h4>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    Limpieza por ultrasonido y calibración de inyectores, cambio de bujías de iridio, limpieza del cuerpo de aceleración y prueba de compresión.
                </p>
            </div>

            {{-- Servicio 5 --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-3 shadow-xs">
                <span class="text-2xl">⚙️</span>
                <h4 class="font-bold text-base text-zinc-900 dark:text-zinc-100">Mantenimiento Correctivo de Motor</h4>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    Reparación de culatas, cambio de kit de distribución (faja/cadena), solución de fugas de refrigerante y reparación integral de transmisión.
                </p>
            </div>

            {{-- Servicio 6 --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-3 shadow-xs">
                <span class="text-2xl">❄️</span>
                <h4 class="font-bold text-base text-zinc-900 dark:text-zinc-100">Aire Acondicionado Automotriz</h4>
                <p class="text-xs text-zinc-500 leading-relaxed">
                    Carga ecológica de gas refrigerante R134a, detección de fugas con tinte UV, desinfección de ductos y reemplazo de compresores.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- 5. CÓMO FUNCIONA EL PROCESO --}}
<section id="proceso" class="py-16 bg-zinc-100/70 dark:bg-zinc-900/50 border-t border-zinc-200 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400">Flujo de Atención</span>
            <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">Atención estructurada en 4 pasos</h2>
            <p class="text-sm sm:text-base text-zinc-500">Garantizamos orden y trazabilidad desde que ingresas hasta la entrega.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 space-y-2 relative">
                <span class="font-mono text-xs font-black text-primary-600 bg-primary-50 dark:bg-primary-950 px-2 py-1 rounded">PASO 01</span>
                <h5 class="font-bold text-sm text-zinc-900 dark:text-white pt-2">Reserva de Cita</h5>
                <p class="text-xs text-zinc-500 leading-relaxed">El cliente selecciona su placa, motivo y horario disponible desde el portal web.</p>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 space-y-2 relative">
                <span class="font-mono text-xs font-black text-primary-600 bg-primary-50 dark:bg-primary-950 px-2 py-1 rounded">PASO 02</span>
                <h5 class="font-bold text-sm text-zinc-900 dark:text-white pt-2">Recepción y Kilometraje</h5>
                <p class="text-xs text-zinc-500 leading-relaxed">El vehículo ingresa al taller, se verifica la orden y se asienta el kilometraje de llegada.</p>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 space-y-2 relative">
                <span class="font-mono text-xs font-black text-primary-600 bg-primary-50 dark:bg-primary-950 px-2 py-1 rounded">PASO 03</span>
                <h5 class="font-bold text-sm text-zinc-900 dark:text-white pt-2">Diagnóstico y Trabajo</h5>
                <p class="text-xs text-zinc-500 leading-relaxed">El técnico evalúa, registra hallazgos y ejecuta el mantenimiento aprobado.</p>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 space-y-2 relative">
                <span class="font-mono text-xs font-black text-primary-600 bg-primary-50 dark:bg-primary-950 px-2 py-1 rounded">PASO 04</span>
                <h5 class="font-bold text-sm text-zinc-900 dark:text-white pt-2">Consolidación en HEV</h5>
                <p class="text-xs text-zinc-500 leading-relaxed">Se liquida la orden y el servicio pasa automáticamente a la Historia Electrónica.</p>
            </div>
        </div>
    </div>
</section>

{{-- 6. CONTACTO, UBICACIÓN Y HORARIOS --}}
<section id="contacto" class="py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-zinc-900 dark:bg-zinc-900 text-white p-8 sm:p-12 border border-zinc-800 shadow-xl flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
            <div class="space-y-4 max-w-xl">
                <span class="text-xs font-bold uppercase tracking-wider text-primary-400">¿Listo para atender tu vehículo?</span>
                <h3 class="text-2xl sm:text-4xl font-extrabold tracking-tight">Visítanos en Automotriz Centeno</h3>
                <p class="text-sm text-zinc-400 leading-relaxed">
                    Contamos con equipamiento computarizado y mecánicos calificados para mantener tu unidad en óptimas condiciones mecánicas.
                </p>

                <div class="space-y-2 text-xs sm:text-sm text-zinc-300 pt-2">
                    <div class="flex items-center gap-3">
                        <span>📍</span>
                        <span>CYC SERVICE SAC — Taller Mecánico Principal, Lima, Perú.</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span>🕒</span>
                        <span>Lunes a Sábado: 08:00 AM – 06:00 PM (Previa Cita)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span>📞</span>
                        <span>Central Telefónica / WhatsApp: +51 999 999 999</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row lg:flex-col gap-3 w-full lg:w-auto shrink-0">
                <a
                    href="{{ route('login') }}"
                    class="px-8 py-4 rounded-xl bg-primary-600 hover:bg-primary-500 text-white font-bold text-center text-sm shadow-md transition-all active:scale-95"
                >
                    Reservar Turno Ahora
                </a>
                <a
                    href="https://wa.me/51999999999"
                    target="_blank"
                    class="px-8 py-4 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-center text-sm border border-white/15 transition-all"
                >
                    Consultar por WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

{{-- 7. FOOTER INSTITUCIONAL --}}
<footer class="border-t border-zinc-200 dark:border-zinc-800 py-8 bg-white dark:bg-zinc-950 text-xs text-zinc-500 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="font-bold text-zinc-800 dark:text-zinc-200">Automotriz Centeno</span>
            <span>•</span>
            <span>CYC SERVICE SAC &copy; {{ date('Y') }}</span>
            <span>•</span>
            <span>Todos los derechos reservados.</span>
        </div>

        <div class="flex items-center gap-6">
            <a href="/login" class="hover:text-zinc-800 dark:hover:text-zinc-200">Portal Cliente</a>
            <a href="/admin" class="hover:text-zinc-800 dark:hover:text-zinc-200">Acceso Taller (Backoffice)</a>
        </div>
    </div>
</footer>

</body>
</html>
