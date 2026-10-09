<?php

use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CircuitoController;
use App\Http\Controllers\ComunicacionController;
use App\Http\Controllers\ConteoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IglesiaController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\PrediccionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
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
    Route::get('/conteos', [ConteoController::class, 'index'])->name('conteos.index');
    Route::get('/conteos/crear', [ConteoController::class, 'create'])->name('conteos.create');
    Route::post('/conteos', [ConteoController::class, 'store'])->name('conteos.store');

    // Gestión operativa incluida en el perfil aprobado.
    Route::resource('actividades', ActividadController::class)
        ->parameters(['actividades' => 'actividad']);
    Route::resource('comunicaciones', ComunicacionController::class)
        ->parameters(['comunicaciones' => 'comunicacion'])
        ->only(['index', 'create', 'store', 'show']);
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');

    // Consulta de iglesias según la jurisdicción del usuario.
    Route::get('/iglesias', [IglesiaController::class, 'index'])->name('iglesias.index');

    // Módulos Estratégicos Distritales y de Administración (Solo Admin y Superintendente)
    Route::middleware('role:admin,distrito')->group(function () {
        Route::resource('circuitos', CircuitoController::class)->except(['show']);
        Route::resource('iglesias', IglesiaController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

        // Carga Masiva de Datos Históricos
        Route::get('/conteos/importar', [ConteoController::class, 'importar'])->name('conteos.importar');
        Route::get('/conteos/descargar-plantilla', [ConteoController::class, 'descargarPlantilla'])->name('conteos.plantilla');
        Route::post('/conteos/importar', [ConteoController::class, 'procesarImportacion'])->name('conteos.procesar-importar');
        Route::post('/conteos/regenerar-ficticios', [ConteoController::class, 'regenerarFicticios'])->name('conteos.regenerar');

        // Modelo Predictivo Holt
        Route::get('/prediccion', [PrediccionController::class, 'index'])->name('prediccion.index');
        Route::post('/prediccion/ejecutar', [PrediccionController::class, 'ejecutar'])->name('prediccion.ejecutar');
        Route::get('/prediccion/{id}/resultado', [PrediccionController::class, 'resultado'])->name('prediccion.resultado');

        // Gestión de Usuarios y Designaciones Pastorales
        Route::resource('usuarios', UserController::class);

        // API distrital: no expone series ni pronósticos a roles locales o de circuito.
        Route::prefix('api')->group(function () {
            Route::get('/serie-historica', [PrediccionController::class, 'apiSerieHistorica']);
            Route::get('/ultima-prediccion', [PrediccionController::class, 'apiUltimaPrediccion']);
        });
    });

    // Se registra después de /iglesias/create para no confundir "create" con un identificador.
    Route::get('/iglesias/{iglesia}', [IglesiaController::class, 'show'])->name('iglesias.show');
});
