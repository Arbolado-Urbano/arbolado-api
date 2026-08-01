<?php

namespace App\Http\Controllers;

use App\Jobs\GenerarPMTiles;

use Illuminate\Http\Request;

class MapaController extends Controller
{
    /**
     * Generar el archivo /public/arboles.pmtiles
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        $force = $request->has('forzar');
        GenerarPMTiles::dispatch($force);
        if (config('queue.default') === 'sync') {
            return response(
                $force
                    ? 'Regeneración del archivo PMTiles finalizada.'
                    : 'Actualización del archivo PMTiles finalizada.'
            );
        } else {
            return response(
                $force
                    ? 'Regeneración del archivo PMTiles iniciada.'
                    : 'Actualización del archivo PMTiles iniciada.');
        }
    }
}
