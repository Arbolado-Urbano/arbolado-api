<?php

namespace App\Http\Controllers;

use \App\Models\Arbol;
use \Illuminate\Http\Request;

class ArbolesController extends Controller
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
   * @param  \Illuminate\Http\Request $request
   * @return Response - JSON con una lista de todas las especies.
   */
    public function list(Request $request)
    {
        $arboles = Arbol::select(['registros.id', 'lat', 'lng', 'especie_id', 'icono'])
        ->join('especies', 'especie_id', '=', 'especies.id');

        if ($request->input('especie_id')) {
            $arboles->where('especie_id', $request->input('especie_id'));
        }

        if ($request->input('user_sabores')) {
            $arboles->where(function ($query) {
                $query->where('comestible', 'Sí')->orWhere('medicinal', 'Sí');
            });
        }

        if ($request->input('user_origen')) {
            $arboles->where('origen', 'like', '%'.$request->input('user_origen').'%');
        }

        if ($request->input('borigen_pampeana')) {
            $arboles->where('region_pampeana', true);
        }

        if ($request->input('borigen_nea')) {
            $arboles->where('region_nea', true);
        }

        if ($request->input('borigen_noa')) {
            $arboles->where('region_noa', true);
        }

        if ($request->input('borigen_cuyana')) {
            $arboles->where('region_cuyana', true);
        }

        if ($request->input('borigen_patagonica')) {
            $arboles->where('region_patagonica', true);
        }

        if (($request->input('user_latlng')) && ($request->input('radio'))) {
            $radio = $request->input('radio');
            $user_latlng = explode(" ", $request->input('user_latlng'));
            $user_lat = $user_latlng[0];
            $user_lng = $user_latlng[1];
            if (($user_lat) && ($user_lng) && is_numeric($user_lat) && is_numeric($user_lng) && (is_numeric($radio))) {
                $arboles->whereRaw("(6371 * acos(cos(radians($user_lat)) * cos(radians(lat)) * cos(radians(lng) - radians($user_lng)) + sin (radians($user_lat)) * sin(radians(lat)))) < $radio / 1000");
            }
        }

        return response()->json($arboles->get());
    }
}
