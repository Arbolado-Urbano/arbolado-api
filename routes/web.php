<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AportesController;
use App\Http\Controllers\ArbolesController;
use App\Http\Controllers\EspeciesController;
use App\Http\Controllers\FuentesController;
use App\Http\Controllers\IdentifyController;

Route::get('/', function () {
    return "Arbolado Urbano API V1.0.0";
});

Route::get('/fuentes/{slug}', [FuentesController::class, 'getTrees']);
Route::get('/especies', [EspeciesController::class, 'list']);
Route::get('/arboles', [ArbolesController::class, 'list']);
Route::get('/arboles/{id}', [ArbolesController::class, 'get']);
Route::post('/arboles', [AportesController::class, 'add']);
Route::post('/identificar', [IdentifyController::class, 'post']);
