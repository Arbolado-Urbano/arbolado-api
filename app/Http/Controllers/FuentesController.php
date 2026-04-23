<?php

namespace App\Http\Controllers;

use App\Models\Arbol;

class FuentesController extends Controller
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
   * Mostar árboles de una fuente
   *
   * @param  $slud - Slug de la fuente
   * @return Response - JSON con una lista de todos los árobles provistos por la fuente.
   */
    public function getTrees($slug)
    {
        $arboles = Arbol::select(['id', 'lat', 'lng', 'especie_id'])
        ->with([
            'species',
            'records',
            'records.source',
        ])->whereHas('records.source', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });

        return response()->json($arboles->get());
    }
}
