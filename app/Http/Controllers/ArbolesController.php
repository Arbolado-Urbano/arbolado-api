<?php

namespace App\Http\Controllers;

use App\Models\Arbol;
use App\Models\Fuente;
use App\Models\Aporte;

use App\Mail\Aporte as AporteCorreo;
use App\Mail\AporteConfirmacion as AporteConfirmacionCorreo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
   * Agregar un nuevo árbol
   *
   * @param  $data - Datos del árbol
   * @return Response - JSON con los detalles del árbol.
   */
    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if ((!isset($data['email']) || !$data['email']) ||
            (!isset($data['name']) || !$data['name']) ||
            (!isset($data['coordinates']) || !$data['coordinates']) ||
            ((!isset($data['species']) || !$data['species']) && (!isset($data['speciesId']) || !$data['speciesId']))
        ) {
            return response('', 400);
        }
        $latLng = explode(',', $data['coordinates']);
        if (count($latLng) < 2) {
            return response('', 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return response('', 400);
        }

        if (!$this->verificarCaptcha($data['captcha'])) {
            return response('', 400);
        }

        try {
            DB::transaction(function () use ($data, $latLng) {
                $url = isset($data['website']) && $data['website'] !== "" ? $data['website'] : null;
                Fuente::upsert([
                    ['nombre' => $data['name'], 'email' => $data['email'], 'url' => $url],
                ], uniqueBy: ['email'], update: ['nombre', 'url']);
                $fuenteId = Fuente::where('email', $data['email'])->first()->id;
                Aporte::create([
                    'lat' => $latLng[0],
                    'lng' => $latLng[1],
                    'especie' => isset($data['species']) && $data['species'] !== "" ? $data['species'] : null,
                    'altura' => isset($data['height']) && $data['height'] !== "" ? $data['height'] : null,
                    'diametro_a_p' => isset($data['diameterTrunk']) && $data['diameterTrunk'] !== "" ? $data['diameterTrunk'] : null,
                    'diametro_copa' => isset($data['diameterCanopy']) && $data['diameterCanopy'] !== "" ? $data['diameterCanopy'] : null,
                    'inclinacion' => isset($data['inclination']) && $data['inclination'] !== "" ? $data['inclination'] : null,
                    'estado_fitosanitario' => isset($data['health']) && $data['health'] !== "" ? $data['health'] : null,
                    'etapa_desarrollo' => isset($data['development']) && $data['development'] !== "" ? $data['development'] : null,
                    'fuente_id' => $fuenteId,
                    'especie_id' => isset($data['speciesId']) && $data['speciesId'] !== "" ? $data['speciesId'] : null,
                ]);
            });

            // Email admin
            $email = new AporteCorreo($data);
            $email->subject('Nuevo aprote Arbolado Urbano');
            if (isset($data['images']) && $data['images'] !== "") {
                try {
                    foreach ($data['images'] as $index => $image) {
                        $imageData = explode(",", $image);
                        $imageType = explode(";", explode(":", $imageData[0])[1])[0];
                        $email->attachData(base64_decode($imageData[1]), "imagen_$index", ['mime' => $imageType]);
                    }
                } catch (\Throwable $th) {
                }
            }
            try {
                Mail::to(config('mail.admin_email'))->send($email);
            } catch (\Throwable $th) {
            }

            // Email usuario
            $emailConfirmacion = new AporteConfirmacionCorreo($data);
            $emailConfirmacion->subject('Nuevo aporte Arbolado Urbano');
            try {
                Mail::to($data['email'])->send($emailConfirmacion);
            } catch (\Throwable $th) {
            }

            return response('', 200);
        } catch (\Throwable $th) {
            return response('', 500);
        }
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

    /**
   * Mostar detalles de un árbol
   *
   * @param  $slud - Slug de la fuente
   * @return Response - JSON con una lista de todos los árobles provistos por la fuente.
   */
    public function getSource($slug)
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

    private function verificarCaptcha($captcha)
    {
        $captchaSecret = env('CAPTCHA_SECRET_KEY');
        $captchaData = "secret=$captchaSecret&response=$captcha";
        $captchaRes = [];
        try {
            $captchaRes = Http::post("https://www.google.com/recaptcha/api/siteverify?$captchaData")->json();
        } catch (\Throwable $th) {
            return false;
        }
        return $captchaRes['success'];
    }
}
