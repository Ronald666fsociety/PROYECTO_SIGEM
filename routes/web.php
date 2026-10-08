<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\IglesiaController;
use App\Http\Controllers\PrediccionController;
use App\Http\Controllers\UserController;

// Authentication routes
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected application routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Membresia
    Route::resource('membresia', MembresiaController::class)->parameters([
        'membresia' => 'miembro',
    ]);
    // Conteos Mensuales (Listado y Registro mensual por iglesia)
    Route::get('/conteos', [App\Http\Controllers\ConteoController::class, 'index'])->name('conteos.index');
    Route::get('/conteos/crear', [App\Http\Controllers\ConteoController::class, 'create'])->name('conteos.create');
    Route::post('/conteos', [App\Http\Controllers\ConteoController::class, 'store'])->name('conteos.store');

    // Iglesias y Circuitos (Acceso estructurado por jurisdicción)
    Route::get('/iglesias', [IglesiaController::class, 'index'])->name('iglesias.index');
    Route::get('/iglesias/{iglesia}', [IglesiaController::class, 'show'])->name('iglesias.show');

    // Módulos Estratégicos Distritales y de Administración (Solo Admin y Superintendente)
    Route::middleware('role:admin,distrito')->group(function () {
        // Carga Masiva de Datos Históricos
        Route::get('/conteos/importar', [App\Http\Controllers\ConteoController::class, 'importar'])->name('conteos.importar');
        Route::get('/conteos/descargar-plantilla', [App\Http\Controllers\ConteoController::class, 'descargarPlantilla'])->name('conteos.plantilla');
        Route::post('/conteos/importar', [App\Http\Controllers\ConteoController::class, 'procesarImportacion'])->name('conteos.procesar-importar');
        Route::post('/conteos/regenerar-ficticios', [App\Http\Controllers\ConteoController::class, 'regenerarFicticios'])->name('conteos.regenerar');

        // Modelo Predictivo Holt
        Route::get('/prediccion', [PrediccionController::class, 'index'])->name('prediccion.index');
        Route::post('/prediccion/ejecutar', [PrediccionController::class, 'ejecutar'])->name('prediccion.ejecutar');
        Route::get('/prediccion/{id}/resultado', [PrediccionController::class, 'resultado'])->name('prediccion.resultado');

        // Gestión de Usuarios y Designaciones Pastorales
        Route::resource('usuarios', UserController::class);
    });

    // API endpoints para integracion
    Route::prefix('api')->group(function () {
        Route::get('/serie-historica', [PrediccionController::class, 'apiSerieHistorica']);
        Route::get('/ultima-prediccion', [PrediccionController::class, 'apiUltimaPrediccion']);
    });
});
