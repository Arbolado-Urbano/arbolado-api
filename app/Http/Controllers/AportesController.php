<?php

namespace App\Http\Controllers;

use App\Models\Fuente;
use App\Models\Aporte;

use App\Services\CaptchaService;

use App\Mail\Aporte as AporteCorreo;
use App\Mail\AporteConfirmacion as AporteConfirmacionCorreo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AportesController extends Controller
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
   * Agregar un nuevo aporte
   *
   * @param  $data - Datos del aporte
   * @return Response - JSON con los detalles del aporte.
   */
    public function add(Request $request, CaptchaService $captchaService)
    {
        $data = json_decode($request->getContent(), true);
        if ((!isset($data['email']) || !$data['email']) ||
            (!isset($data['name']) || !$data['name']) ||
            (!isset($data['coordinates']) || !$data['coordinates']) ||
            ((!isset($data['species']) || !$data['species']) && (!isset($data['speciesId']) || !$data['speciesId']))
        ) {
            return response('', 422);
        }
        
        $latLng = explode(',', $data['coordinates']);
        if (count($latLng) < 2) {
            return response('', 422);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return response('', 422);
        }

        if (!$captchaService->verify($data['captcha'])) {
            return response('', 422);
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
                    'notas' => isset($data['notes']) && $data['notes'] !== "" ? $data['notes'] : null,
                ]);
            });

            // Email admin
            $email = new AporteCorreo($data);
            $email->subject('Nuevo aporte Arbolado Urbano');
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
}
