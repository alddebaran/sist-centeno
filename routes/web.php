<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Redirección inteligente según el rol al iniciar sesión
    Route::get('dashboard', function () {
        if (auth()->user()->role === 'cliente') {
            return redirect()->route('portal.cliente');
        }

        return redirect('/admin');
    })->name('dashboard');

    // Portal del Cliente (Livewire 4 SFC + Flux)
    Route::livewire('/portal', 'portal-cliente')->name('portal.cliente');
});

require __DIR__.'/settings.php';
