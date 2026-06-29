<?php

use Illuminate\Support\Facades\Schedule;

use App\Models\Aporte;
use App\Models\Especie;

use App\Mail\Pendientes as PendientesCorreo;

use App\Jobs\GenerarPMTiles;

// Informar al administrador de aportes y especies pendientes de revisión y aprobación
Schedule::call(function () {
    $aportes = Aporte::select(['id'])->where('cargado', 0)->get();
    $especies = Especie::select(['id'])->whereNull('familia_id')->get();
    if (count($aportes) > 0 || count($especies) > 0) {
        $email = new PendientesCorreo($especies, $aportes);
        $email->subject('Revisiones pendientes | Arbolado Urbano');
        try {
            Mail::to(config('mail.admin_email'))->send($email);
        } catch (\Throwable $th) {
            \Log::error('Job error - error al enviar informe diario al administrador:');
            \Log::error($th);
        }
    }
})->daily();

// Generar el archivo arboles.pmtiles
Schedule::call(function () {
    GenerarPMTiles::dispatch(false);
})->daily();