<?php

namespace App\Http\Controllers;

use App\Models\Especie;
use App\Models\Arbol;
use App\Models\Registro;
use App\Models\Usuario;

use App\Services\CaptchaService;

use App\Mail\NuevoArbol as NuevoArbolCorreo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
        ->with('species')->whereNull('removido');

        if (!empty($request->input('especie_id')) && ($request->input('especie_id'))) {
            $arboles->where('especie_id', $request->input('especie_id'));
        }

        if (!empty($request->input('user_sabores')) && ($request->input('user_sabores'))) {
            $arboles->whereHas('species', function($query) {
                $query->where('comestible', 'Sí')->orWhere('medicinal', 'Sí');
            });
        }

        if (!empty($request->input('user_origen')) && ($request->input('user_origen'))) {
            $arboles->whereHas('species', function($query) use ($request) {
                $query->where('origen', 'like', '%'.$request->input('user_origen').'%');
            });
        }

        if (!empty($request->input('borigen_pampeana')) && ($request->input('borigen_pampeana'))) {
            $arboles->whereHas('species', function($query) {
                $query->where('region_pampeana', true);
            });
        }

        if (!empty($request->input('borigen_nea')) && ($request->input('borigen_nea'))) {
            $arboles->whereHas('species', function($query) {
                $query->where('region_nea', true);
            });
        }

        if (!empty($request->input('borigen_noa')) && ($request->input('borigen_noa'))) {
            $arboles->whereHas('species', function($query) {
                $query->where('region_noa', true);
            });
        }

        if (!empty($request->input('borigen_cuyana')) && ($request->input('borigen_cuyana'))) {
            $arboles->whereHas('species', function($query) {
                $query->where('region_cuyana', true);
            });
        }

        if (!empty($request->input('borigen_patagonica')) && ($request->input('borigen_patagonica'))) {
            $arboles->whereHas('species', function($query) {
                $query->where('region_patagonica', true);
            });
        }

        if ((!empty($request->input('user_latlng'))) &&
          ($request->input('user_latlng')) &&
          (!empty($request->input('radio'))) &&
          ($request->input('radio'))
        ) {
            $radio = $request->input('radio');
            $user_latlng = explode(' ', $request->input('user_latlng'));
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

     /**
   * Agregar un nuevo árbol
   *
   * @param  \Illuminate\Http\Request $request
   * @return Response
   */
    public function add(Request $request, CaptchaService $captchaService)
    {
        $data = $request->all();
        $especieId = isset($data['speciesId']) && $data['speciesId'] !== '' ? $data['speciesId'] : null;
        if ((!isset($data['code']) || !$data['code']) ||
            (!isset($data['coordinates']) || !$data['coordinates']) ||
            ((!isset($data['species']) || !$data['species']) && (!$especieId))
        ) {
            return response('', 422);
        }
        
        $latLng = explode(',', $data['coordinates']);
        if (count($latLng) < 2) {
            return response('', 422);
        }

        if (!$captchaService->verify($data['captcha'])) {
            return response('', 422);
        }

        $user = Usuario::select(['id', 'nombre', 'codigo', 'fuente_id'])->where('usuarios.codigo', $data['code'])->first();
        if (!$user) return response('', 401);

        try {
            DB::transaction(function () use ($data, $latLng, $especieId, $user, $request) {
                // Si se ingresó una nueva especie crearla
                if (!$especieId) {
                    $especieId = Especie::firstOrCreate([
                        // Por el chequeo inicial si "speciesId" no está definido entonces "species" si está definido.
                        'nombre_cientifico' => $data['species'],
                    ])->id;
                }
                $index = 1;
                $idCensoBase = strtoupper("$data[block]$data[orientation]");
                do {
                    $idCenso = "$idCensoBase$index";
                    $arbol = Arbol::select(['arboles.id'])->where('arboles.id_censo', $idCenso)->first();
                    $index++;
                } while ($arbol);
                $treeData = [
                    'lat' => $latLng[0],
                    'lng' => $latLng[1],
                    'id_censo' => $idCenso,
                    'localidad' => 'Colón',
                    'especie_id' => $especieId,
                ];
                $arbol = Arbol::create($treeData);
                $recordData = [
                    'altura' => isset($data['height']) && $data['height'] !== '' ? $data['height'] : null,
                    'diametro_a_p' => isset($data['diameterTrunk']) && $data['diameterTrunk'] !== '' ? $data['diameterTrunk'] : null,
                    'diametro_copa' => isset($data['diameterCanopy']) && $data['diameterCanopy'] !== '' ? $data['diameterCanopy'] : null,
                    'inclinacion' => isset($data['inclination']) && $data['inclination'] !== '' ? $data['inclination'] : null,
                    'estado_fitosanitario' => isset($data['health']) && $data['health'] !== '' ? $data['health'] : null,
                    'etapa_desarrollo' => isset($data['development']) && $data['development'] !== '' ? $data['development'] : null,
                    'notas' => isset($data['notes']) && $data['notes'] !== '' ? $data['notes'] : null,
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
            return response('', 200);
        } catch (\Throwable $th) {
            \Log::error('Nuevo árbol - error al crear nuevo árbol:');
            \Log::error($th);
            return response('', 500);
        }
    }
}
