<?php

namespace App\Http\Controllers;

use \App\Models\Especie;

class EspeciesController extends Controller
{
    /**
     * Listar todas las especies
     *
     * @return \Illuminate\Http\Response - JSON con una lista de todas las especies.
     */
    public function list()
    {
        $especies = Especie::select([
            'nombre_cientifico',
            'nombre_comun',
            'url',
            'comestible',
            'origen',
            'region_pampeana',
            'region_nea',
            'region_noa',
            'region_cuyana',
            'region_patagonica',
        ])->orderBy('nombre_cientifico')->get();
        $filtered = $especies->map(fn($especie) => array_filter(
            $especie->toArray(),
            fn($value) => $value !== null && $value !== '' && $value !== 0
        ));
        return response()->json($filtered);
    }
}
