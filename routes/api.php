<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AportesController;
use App\Http\Controllers\ArbolesController;
use App\Http\Controllers\EspeciesController;
use App\Http\Controllers\FuentesController;
use App\Http\Controllers\UsuariosController;

Route::get('/', function () {
    return "Arbolado Urbano API V1.5.2";
});

Route::get('/fuentes/{slug}', [FuentesController::class, 'getTrees']);
Route::get('/especies', [EspeciesController::class, 'list']);
Route::get('/arboles', [ArbolesController::class, 'list']);
Route::get('/arboles/{id}', [ArbolesController::class, 'get']);
Route::post('/arboles', [ArbolesController::class, 'add']);
Route::post('/aportes', [AportesController::class, 'add']);
Route::post('/usuarios', [UsuariosController::class, 'get']);
Route::post('/identificar', [EspeciesController::class, 'identify']);

// Consultas administrativas protegidas (solo lectura).
Route::get('/admin/aportes/pendientes', [\App\Http\Controllers\Admin\AportesPendientesController::class, 'index'])
    ->middleware('admin.token');

Route::post('/admin/aportes/{id}/aprobar', [\App\Http\Controllers\Admin\ModerarAporteController::class, 'aprobar'])->whereNumber('id')->middleware('admin.token');
Route::post('/admin/aportes/{id}/rechazar', [\App\Http\Controllers\Admin\ModerarAporteController::class, 'rechazar'])->whereNumber('id')->middleware('admin.token');

Route::get('/admin/fuentes/{fuenteId}/revision', [\App\Http\Controllers\Admin\FinalizarRevisionController::class, 'preview'])->whereNumber('fuenteId')->middleware('admin.token');
Route::post('/admin/fuentes/{fuenteId}/finalizar', [\App\Http\Controllers\Admin\FinalizarRevisionController::class, 'finalizar'])->whereNumber('fuenteId')->middleware('admin.token');
