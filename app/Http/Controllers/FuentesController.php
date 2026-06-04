<?php

namespace App\Http\Controllers;

use App\Models\Fuente;

class FuentesController extends Controller
{
    /**
     * Obtener el ID de una fuente.
     *
     * @param  $slug - Slug de la fuente.
     * @return \Illuminate\Http\Response - JSON con el ID la fuente.
     */
    public function getTrees($slug)
    {
        $source = Fuente::select(['id'])->where('slug', $slug)->first();
        if (!$source) abort(404);
        return response()->json($source);
    }
}
