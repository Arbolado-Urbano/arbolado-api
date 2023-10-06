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
   * Listar todos los árboles
   *
   * @param  \Illuminate\Http\Request $request
   * @return Response - JSON con una lista de todos los árobles.
   */
    public function list(Request $request)
    {
        $arboles = Arbol::select(['id', 'lat', 'lng', 'especie_id'])
        ->with('species');

        if (!empty($request->input('especie_id')) && ($request->input('especie_id'))) {
            $arboles->where('especie_id', $request->input('especie_id'));
        }

        if (!empty($request->input('user_sabores')) && ($request->input('user_sabores'))) {
            $arboles->where(function ($query) {
                $query->where('comestible', 'Sí')->orWhere('medicinal', 'Sí');
            });
        }

        if (!empty($request->input('user_origen')) && ($request->input('user_origen'))) {
            $arboles->where('origen', 'like', '%'.$request->input('user_origen').'%');
        }

        if (!empty($request->input('borigen_pampeana')) && ($request->input('borigen_pampeana'))) {
            $arboles->where('region_pampeana', true);
        }

        if (!empty($request->input('borigen_nea')) && ($request->input('borigen_nea'))) {
            $arboles->where('region_nea', true);
        }

        if (!empty($request->input('borigen_noa')) && ($request->input('borigen_noa'))) {
            $arboles->where('region_noa', true);
        }

        if (!empty($request->input('borigen_cuyana')) && ($request->input('borigen_cuyana'))) {
            $arboles->where('region_cuyana', true);
        }

        if (!empty($request->input('borigen_patagonica')) && ($request->input('borigen_patagonica'))) {
            $arboles->where('region_patagonica', true);
        }

        if ((!empty($request->input('user_latlng'))) &&
          ($request->input('user_latlng')) &&
          (!empty($request->input('radio'))) &&
          ($request->input('radio'))
        ) {
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

    /**
   * Mostar detalles de un árbol
   *
   * @param  $id - ID del árbol
   * @return Response - JSON con los detalles del árbol.
   */
    public function get($id)
    {
        $tree = Arbol::select([
          'arboles.id',
          'arboles.calle',
          'arboles.calle_altura',
          'arboles.espacio_verde',
          'arboles.especie_id',
          'arboles.streetview',
          'arboles.lat',
          'arboles.lng',
        ])
        ->with([
            'species',
            'species.family',
            'species.type',
            'records',
            'records.source',
        ])->where('arboles.id', $id)->first();
        if (!$tree) return response('', 404);
        return response()->json($tree);
    }
}
