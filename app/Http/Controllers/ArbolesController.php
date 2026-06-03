<?php

namespace App\Http\Controllers;

use App\Models\Especie;
use App\Models\Arbol;
use App\Models\Registro;
use App\Models\Usuario;

use App\Mail\NuevoArbol as NuevoArbolCorreo;

use App\Rules\CaptchaRule;

use App\Jobs\GenerarPMTiles;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ArbolesController extends Controller
{
    /**
     * Generar el archivo /public/arboles.pmtiles
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        GenerarPMTiles::dispatch();
        if (config('queue.default') === 'sync') {
            return response('Generación de archivo PMTiles finalizada.');
        } else {
            return response("Generación de archivo PMTiles iniciada.");
        }
    }

    /**
     * Mostrar los detalles de un árbol
     *
     * @param  $id - ID del árbol
     * @return \Illuminate\Http\Response - JSON con los detalles del árbol.
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
        if (!$tree) abort(404);
        return response()->json($tree);
    }

    /**
     * Agregar un nuevo árbol
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        $data = $request->validate([
            'code'           => 'required|string',
            'coordinates'    => ['required', 'regex:/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/'],
            'species'        => 'nullable|string|required_without:speciesUrl',
            'speciesUrl'     => 'nullable|string|required_without:species',
            'captcha'        => ['required', new CaptchaRule()],
            'block'          => 'required|string',
            'orientation'    => 'required|string',
            'height'         => 'nullable|string',
            'diameterTrunk'  => 'nullable|string',
            'diameterCanopy' => 'nullable|string',
            'inclination'    => 'nullable|string',
            'health'         => 'nullable|string',
            'development'    => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $user = Usuario::select(['id', 'nombre', 'codigo', 'fuente_id'])->where('usuarios.codigo', $data['code'])->first();
        if (!$user) abort(401);

        try {
            DB::transaction(function () use ($data, $user, $request) {
                $especieId = null;
                $especieUrl = $data['speciesUrl'] ?? null;
                if ($especieUrl) {
                    $especie = Especie::select(['id'])->where('url', $especieUrl)->first();
                    if (!$especie) abort(404);
                    $especieId = $especie->id;
                } else {
                    // Si se ingresó una nueva especie crearla
                    $especieId = Especie::firstOrCreate([
                        // Por el chequeo inicial si "speciesUrl" no está definido entonces "species" si está definido.
                        'nombre_cientifico' => $data['species'],
                    ])->url;
                }
                $index = 1;
                $idCensoBase = strtoupper("$data[block]$data[orientation]");
                do {
                    $idCenso = "$idCensoBase$index";
                    $arbol = Arbol::select(['arboles.id'])->where('arboles.id_censo', $idCenso)->first();
                    $index++;
                } while ($arbol);
                $latLng = explode(',', $data['coordinates']);
                $treeData = [
                    'lat' => $latLng[0],
                    'lng' => $latLng[1],
                    'id_censo' => $idCenso,
                    'localidad' => 'Colón',
                    'especie_id' => $especieId,
                ];
                $arbol = Arbol::create($treeData);
                $recordData = [
                    'altura' => $data['height'] ?? null,
                    'diametro_a_p' => $data['diameterTrunk'] ?? null,
                    'diametro_copa' => $data['diameterCanopy'] ?? null,
                    'inclinacion' => $data['inclination'] ?? null,
                    'estado_fitosanitario' => $data['health'] ?? null,
                    'etapa_desarrollo' => $data['development'] ?? null,
                    'notas' => $data['notes'] ?? null,
                    'arbol_id' => $arbol->id,
                    'usuario_id' => $user->id,
                    'fuente_id' => $user->fuente_id,
                ];
                Registro::create($recordData);
                // Email admin
                $especie = Especie::select(['nombre_cientifico', 'nombre_comun'])->where('id', $especieId)->first();
                $emailData = array_merge($treeData, $recordData, [
                    'block' => $data['block'],
                    'orientation' => $data['orientation'],
                    'especie_nombre_cientifico' => $especie->nombre_cientifico,
                    'especie_nombre_comun' => $especie->nombre_comun,
                    'censista_nombre' => $user->nombre,
                    'censista_codigo' => $user->codigo,
                ]);
                $email = new NuevoArbolCorreo($emailData);
                $email->subject('Nuevo árbol | Arbolado Urbano');
                if ($request->hasFile('images')) {
                    $images = $request->file('images');
                    try {
                        foreach ($images as $index => $image) {
                            $imageName = $idCenso.'-'.($index + 1);
                            $email->attach($image->getRealPath(), ['as' => $imageName, 'mime' => $image->getMimeType()]);
                        }
                    } catch (\Throwable $th) {
                        \Log::error('Nuevo árbol - error adjuntando fotos para email:');
                        \Log::error($th);
                    }
                }
                try {
                    Mail::to(config('mail.admin_email'))->send($email);
                } catch (\Throwable $th) {
                    \Log::error('Nuevo árbol - error al enviar email:');
                    \Log::error($th);
                }
            });
            // Regenerar el archivo pmtiles
            GenerarPMTiles::dispatch();
            return response()->json();
        } catch (\Throwable $th) {
            \Log::error('Nuevo árbol - error al crear nuevo árbol:');
            \Log::error($th);
            abort(500);
        }
    }
}
