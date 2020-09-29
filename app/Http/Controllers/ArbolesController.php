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

    /**
   * Listar todas las especies
   *
   * @param  $id
   * @return Response - JSON con los detalles del árbol.
   */
    public function get($id)
    {
        $arbol = Arbol::select([
          'registros.id',
          'registros.calle',
          'registros.calle_altura',
          'registros.altura',
          'registros.espacio_verde',
          'registros.especie_id',
          'registros.fecha_creacion',
          'registros.streetview',
          'registros.lat',
          'registros.lng',
          'especies.nombre_cientifico',
          'especies.nombre_comun',
          'especies.origen',
          'especies.region_pampeana',
          'especies.region_nea',
          'especies.region_noa',
          'especies.region_cuyana',
          'especies.region_patagonica',
          'especies.procedencia_exotica',
          'tipos.tipo',
          'familias.familia',
          'fuentes.nombre',
          'fuentes.descripcion',
          'fuentes.url',
          'fuentes.facebook',
          'fuentes.twitter',
          'fuentes.instagram',
        ])
        ->join('especies', 'especie_id', '=', 'especies.id')
        ->join('tipos', 'tipos.id', '=', 'especies.tipo_id')
        ->join('familias', 'familias.id', '=', 'especies.familia_id')
        ->join('fuentes', 'fuentes.id', '=', 'registros.fuente_id')
        ->where('registros.id', $id);

        return response()->json($arbol->first());
    }
}
