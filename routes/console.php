<?php

use Illuminate\Support\Facades\Schedule;

use App\Models\Aporte;
use App\Models\Especie;

use App\Mail\Pendientes as PendientesCorreo;

// Informar al administrador de aportes y especies pendientes de revisión y aprobación
Schedule::call(function () {
    $aportesCount = count(Aporte::select(['id'])->where('cargado', 0)->get());
    $especiesCount = count(Especie::select(['id'])->whereNull('familia_id')->get());
    \Log::debug($aportesCount);
    \Log::debug($especiesCount);
    if ($aportesCount > 0 || $especiesCount > 0) {
        $email = new PendientesCorreo($especiesCount, $aportesCount);
        $email->subject('Revisiones pendientes | Arbolado Urbano');
        try {
            Mail::to(config('mail.admin_email'))->send($email);
            } catch (\Throwable $th) {
            \Log::debug($th);
        }
    }
})->daily();