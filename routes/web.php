<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\PlanCuotaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TasaInteresController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard',     [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/pdf', [DashboardController::class, 'pdf'])->name('dashboard.pdf');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/clientes',               [ClienteController::class, 'index'])  ->name('clientes.index');
    Route::post('/clientes',              [ClienteController::class, 'store'])  ->name('clientes.store');
    Route::put('/clientes/{cliente}',     [ClienteController::class, 'update']) ->name('clientes.update');
    Route::delete('/clientes/{cliente}',  [ClienteController::class, 'destroy'])->name('clientes.destroy');

    Route::get('/productos',              [ProductoController::class, 'index'])  ->name('productos.index');
    Route::post('/productos',             [ProductoController::class, 'store'])  ->name('productos.store');
    Route::put('/productos/{producto}',   [ProductoController::class, 'update']) ->name('productos.update');
    Route::delete('/productos/{producto}',[ProductoController::class, 'destroy'])->name('productos.destroy');

    Route::get('/ventas',  [VentaController::class, 'index'])->name('ventas.index');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');

    Route::get('/cuotas',                    [PlanCuotaController::class, 'index'])->name('cuotas.index');
    Route::post('/cuotas/{planCuota}/pagar', [PlanCuotaController::class, 'pagar'])->name('cuotas.pagar');

    Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index');

    Route::get('/tasas',                 [TasaInteresController::class, 'index'])  ->name('tasas.index');
    Route::post('/tasas',                [TasaInteresController::class, 'store'])  ->name('tasas.store');
    Route::put('/tasas/{tasaIntere}',    [TasaInteresController::class, 'update']) ->name('tasas.update');
    Route::delete('/tasas/{tasaIntere}', [TasaInteresController::class, 'destroy'])->name('tasas.destroy');
});

require __DIR__.'/auth.php';
