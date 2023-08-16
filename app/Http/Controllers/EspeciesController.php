<?php

namespace App\Http\Controllers;

use \App\Models\Especie;

class EspeciesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
   * Listar todas las especies
   *
   * @return Response - JSON con una lista de todas las especies.
   */
    public function list()
    {
        $especies = Especie::select(['nombre_cientifico', 'nombre_comun', 'url', 'especies.id'])
        ->join('registros', 'registros.especie_id', '=', 'especies.id')
        ->groupBy(['especies.id', 'nombre_cientifico', 'nombre_comun', 'url'])
        ->orderBy('nombre_cientifico');
        return response()->json($especies->get());
    }
}
