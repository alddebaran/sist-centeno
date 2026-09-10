<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuarios Administrativos y Técnicos del Taller
        $admin = User::firstOrCreate(
            ['email' => 'admin@centeno.com'],
            [
                'name' => 'Administrador Centeno',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'recepcion@centeno.com'],
            [
                'name' => 'Recepcionista Taller',
                'password' => Hash::make('password'),
                'role' => 'recepcionista',
            ]
        );

        User::firstOrCreate(
            ['email' => 'mecanico@centeno.com'],
            [
                'name' => 'Técnico Especialista',
                'password' => Hash::make('password'),
                'role' => 'tecnico',
            ]
        );

        // 2. Datos base con contexto local
        $nombres = [
            'Juan', 'Carlos', 'Miguel', 'Luis', 'Jorge', 'José', 'Diego', 'Brayan', 'Piero', 'Christian',
            'Anderson', 'Renato', 'Julio', 'Víctor', 'Manuel', 'Rosa', 'María', 'Carmen', 'Ana', 'Lucía',
            'Andrea', 'Valeria', 'Fiorella', 'Milagros', 'Patricia', 'Diana', 'Claudia', 'Sandra', 'Roxana', 'Gisella'
        ];

        $apellidos = [
            'Quispe', 'Flores', 'Rodríguez', 'Sánchez', 'García', 'Centeno', 'Castillo', 'Carrasco', 'Aguirre',
            'Mendoza', 'Rojas', 'Huamán', 'Mamani', 'Chávez', 'Ramos', 'Torres', 'Díaz', 'Gómez', 'Vargas',
            'Espinoza', 'Fernández', 'Paredes', 'Morales', 'Gutiérrez', 'Castro', 'Romero', 'Herrera', 'Medina'
        ];

        $marcasModelos = [
            'Toyota' => ['Yaris', 'Corolla', 'Hilux', 'RAV4', 'Etios', 'Rush'],
            'Hyundai' => ['Accent', 'Tucson', 'Elantra', 'Creta', 'Grand i10', 'Santa Fe'],
            'Kia' => ['Rio', 'Cerato', 'Sportage', 'Seltos', 'Picanto', 'Soluto'],
            'Nissan' => ['Versa', 'Sentra', 'Frontier', 'Kicks', 'X-Trail', 'Tiida'],
            'Chevrolet' => ['Sail', 'Tracker', 'Onix', 'Colorado', 'Captiva'],
            'Suzuki' => ['Swift', 'Grand Vitara', 'Jimny', 'Baleno', 'Ertiga'],
            'Volkswagen' => ['Gol', 'Amarok', 'Polo', 'T-Cross', 'Tiguan'],
            'Honda' => ['Civic', 'CR-V', 'HR-V', 'City', 'Fit'],
        ];

        $colores = ['Blanco', 'Gris Plata', 'Negro Perlado', 'Rojo Metálico', 'Azul Eléctrico', 'Grafito', 'Dorado'];

        $motivos = [
            'Mantenimiento preventivo de los 10,000 km',
            'Mantenimiento preventivo de los 20,000 km',
            'Cambio de aceite, filtro y revisión de niveles',
            'Revisión y cambio de pastillas de freno delanteras',
            'Afinamiento electrónico y limpieza de inyectores',
            'Ruido metálico en suspensión delantera al pasar baches',
            'El vehículo pierde fuerza al acelerar en pendientes',
            'Testigo de Check Engine encendido en tablero',
            'Recarga y mantenimiento del sistema de aire acondicionado',
            'Diagnóstico general y escaneo por computadora',
            'Cambio de kit de embrague y purgado',
            'Alineamiento computarizado y balanceo de 4 ruedas',
            'Revisión general previa a viaje interprovincial',
        ];

        $estados = ['pendiente', 'confirmada', 'reprogramada', 'cancelada', 'finalizada'];
        $motivosCancelacion = [
            'El cliente solicitó cancelar por motivos laborales.',
            'Venta del vehículo por parte del cliente.',
            'El cliente no podrá asistir en la fecha programada.',
            'Duplicidad de solicitud telefónica.',
        ];

        $horas = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00'];

        // 3. Generación de 50 Clientes y 100 Vehículos (2 vehículos por cliente)
        $clientesCreados = [];
        $vehiculosCreados = [];
        $placasGeneradas = [];

        for ($i = 1; $i <= 50; $i++) {
            $nom = $nombres[array_rand($nombres)];
            $ape1 = $apellidos[array_rand($apellidos)];
            $ape2 = $apellidos[array_rand($apellidos)];
            $dni = str_pad(70000000 + ($i * 137), 8, '0', STR_PAD_LEFT);

            $cliente = Client::create([
                'tipo_documento' => 'DNI',
                'numero_documento' => $dni,
                'nombres' => $nom,
                'apellidos' => "{$ape1} {$ape2}",
                'telefono' => '9' . rand(10000000, 99999999),
                'email' => strtolower(substr($nom, 0, 1) . $ape1 . $i . '@gmail.com'),
                'direccion' => 'Av. ' . $apellidos[array_rand($apellidos)] . ' #' . rand(100, 999) . ', Lima',
            ]);

            $clientesCreados[] = $cliente;

            // Cada cliente tendrá exactamente 2 vehículos (Total: 100 vehículos)
            for ($v = 1; $v <= 2; $v++) {
                // Placa única con formato peruano ABC-123
                do {
                    $letras = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
                    $numeros = rand(100, 999);
                    $placa = "{$letras}-{$numeros}";
                } while (in_array($placa, $placasGeneradas));

                $placasGeneradas[] = $placa;

                $marca = array_rand($marcasModelos);
                $modelo = $marcasModelos[$marca][array_rand($marcasModelos[$marca])];

                $vehiculo = Vehicle::create([
                    'client_id' => $cliente->id,
                    'placa' => $placa,
                    'marca' => $marca,
                    'modelo' => $modelo,
                    'anio' => (string) rand(2012, 2024),
                    'color' => $colores[array_rand($colores)],
                    'kilometraje' => (string) (rand(10, 180) * 1000),
                ]);

                $vehiculosCreados[] = $vehiculo;
            }
        }

        // 4. Generación de 100 Citas congruentes (Cliente -> Su propio Vehículo)
        for ($c = 1; $c <= 100; $c++) {
            // Selecciona un vehículo al azar y asigna su cliente dueño correspondiente
            $vehiculoSeleccionado = $vehiculosCreados[array_rand($vehiculosCreados)];
            $clienteDueno = $vehiculoSeleccionado->client;

            // Fechas en un rango de: 15 días atrás hasta 15 días hacia el futuro
            $diasOffset = rand(-15, 15);
            $fechaCita = Carbon::now()->addDays($diasOffset)->format('Y-m-d');
            $horaCita = $horas[array_rand($horas)];

            // Si la fecha ya pasó, normalmente está finalizada o cancelada
            if ($diasOffset < 0) {
                $estado = rand(0, 10) > 2 ? 'finalizada' : 'cancelada';
            } else {
                $estado = $estados[array_rand($estados)];
            }

            Appointment::create([
                'client_id' => $clienteDueno->id,
                'vehicle_id' => $vehiculoSeleccionado->id,
                'appointment_date' => $fechaCita,
                'appointment_time' => $horaCita,
                'reason' => $motivos[array_rand($motivos)],
                'status' => $estado,
                'cancellation_reason' => ($estado === 'cancelada') ? $motivosCancelacion[array_rand($motivosCancelacion)] : null,
            ]);
        }
        /*// User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/
    }
}
