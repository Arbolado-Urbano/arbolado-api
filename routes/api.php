<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AportesController;
use App\Http\Controllers\ArbolesController;
use App\Http\Controllers\EspeciesController;
use App\Http\Controllers\FuentesController;
use App\Http\Controllers\MapaController;
use App\Http\Controllers\UsuariosController;

Route::get('/', function () {
    return "Arbolado Urbano API V1.5.1";
});

Route::get('/fuentes/{slug}', [FuentesController::class, 'getTrees']);
Route::get('/especies', [EspeciesController::class, 'list']);
Route::get('/mapa', [MapaController::class, 'generate']);
Route::get('/arboles/{id}', [ArbolesController::class, 'get']);
Route::post('/usuarios', [UsuariosController::class, 'get']);
Route::post('/arboles', [ArbolesController::class, 'add']);
Route::post('/aportes', [AportesController::class, 'add']);
Route::post('/identificar', [EspeciesController::class, 'identify']);
