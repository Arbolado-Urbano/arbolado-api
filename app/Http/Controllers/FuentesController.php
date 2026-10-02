<?php

namespace App\Http\Controllers;

use App\Models\Fuente;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuentesController extends Controller
{
    /**
     * Obtener los árboles de una fuente.
     *
     * @param  $slug - Slug de la fuente.
     * @return \Illuminate\Http\Response - JSON con el listado de árboles.
     */
    public function getTrees($slug, Request $request)
    {
        $source = Fuente::select(['id'])->where('slug', $slug)->first();
        if (!$source) abort(404);
        $headers = [
            'Content-Type'      => 'application/json',
            'X-Accel-Buffering' => 'no', // Disables buffering in Nginx
            'Cache-Control'     => 'no-cache',
        ];

        return response()->stream(function () use ($request, $source) {

            $query = DB::table('arboles')
            ->join('registros', 'registros.arbol_id', '=', 'arboles.id')
            ->whereNull('arboles.removido')
            ->where('registros.fuente_id', '=', $source->id)
            ->select(
                'arboles.id',
                'arboles.lat',
                'arboles.lng',
                'arboles.especie_id',
            );

            echo '[';
            $first = true;
            $query->chunkById(500, function ($chunk) use (&$first) {
                $rows = [];
                foreach ($chunk as $row) {
                    $rows[] = [
                        'id'      => $row->id,
                        'lat'     => $row->lat,
                        'lng'     => $row->lng,
                        'species' => $row->especie_id,
                    ];
                }
                echo ($first ? '' : ',') . substr(json_encode($rows), 1, -1);
                $first = false;

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }, 'arboles.id', 'id');
            echo ']';
        }, 200, $headers);
    }
}
