<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Si es personal del taller va a Filament; si es cliente ve su dashboard con KPIs
    Route::get('dashboard', function () {
        if (auth()->user()->role !== 'cliente') {
            return redirect('/admin');
        }

        return view('dashboard');
    })->name('dashboard');

    // Módulos del Cliente (Pages con Livewire 4 SFC)
    Route::livewire('mis-vehiculos', 'pages::client.vehicles')->name('vehicles');
    Route::livewire('historial-citas', 'pages::client.appointments')->name('appointments');

});

require __DIR__.'/settings.php';
