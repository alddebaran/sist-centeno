<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Diagnósticos técnicos (HU06)
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Técnico que evaluó
            $table->text('details'); // Hallazgos técnicos
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
        });

        // 2. Trabajos de Mantenimiento y liquidación (HU07)
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnosis_id')->constrained('diagnoses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Mecánico responsable
            $table->enum('maintenance_type', ['preventivo', 'correctivo']);
            $table->decimal('budgeted_cost', 10, 2)->default(0); // Presupuesto estimado
            $table->decimal('labor_cost', 10, 2)->default(0);    // Mano de obra
            $table->decimal('total_cost', 10, 2)->default(0);    // Costo final liquidado
            $table->text('procedures');                           // Procedimientos ejecutados
            $table->text('observations')->nullable();             // Recomendaciones
            $table->dateTime('entry_date');                       // Ingreso a taller
            $table->dateTime('delivery_date')->nullable();        // Entrega al cliente
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('diagnoses');
    }
};
