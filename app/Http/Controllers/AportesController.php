<?php

namespace App\Http\Controllers;

use App\Models\Fuente;
use App\Models\Aporte;
use App\Models\Especie;

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
   * @param  \Illuminate\Http\Request $request
   * @return Response
   */
    public function add(Request $request, CaptchaService $captchaService)
    {
        $data = $request->all();
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
                $url = isset($data['website']) && $data['website'] !== '' ? $data['website'] : null;
                Fuente::upsert([
                    ['nombre' => $data['name'], 'email' => $data['email'], 'url' => $url],
                ], uniqueBy: ['email'], update: ['nombre', 'url']);
                $fuenteId = Fuente::where('email', $data['email'])->first()->id;
                Aporte::create([
                    'lat' => $latLng[0],
                    'lng' => $latLng[1],
                    'especie' => isset($data['species']) && $data['species'] !== '' ? $data['species'] : null,
                    'altura' => isset($data['height']) && $data['height'] !== '' ? $data['height'] : null,
                    'diametro_a_p' => isset($data['diameterTrunk']) && $data['diameterTrunk'] !== '' ? $data['diameterTrunk'] : null,
                    'diametro_copa' => isset($data['diameterCanopy']) && $data['diameterCanopy'] !== '' ? $data['diameterCanopy'] : null,
                    'inclinacion' => isset($data['inclination']) && $data['inclination'] !== '' ? $data['inclination'] : null,
                    'estado_fitosanitario' => isset($data['health']) && $data['health'] !== '' ? $data['health'] : null,
                    'etapa_desarrollo' => isset($data['development']) && $data['development'] !== '' ? $data['development'] : null,
                    'fuente_id' => $fuenteId,
                    'especie_id' => isset($data['speciesId']) && $data['speciesId'] !== '' ? $data['speciesId'] : null,
                    'notas' => isset($data['notes']) && $data['notes'] !== '' ? $data['notes'] : null,
                ]);
            });

            // Email admin
            if (isset($data['speciesId']) && $data['speciesId'] !== '') {
                $especie = Especie::select(['nombre_cientifico', 'nombre_comun'])->where('id', $data['speciesId'])->first();
                $data['species'] = $especie->nombre_comun ? $especie->nombre_comun . ' (' . $especie->nombre_cientifico . ')' : $especie->nombre_cientifico;
            }
            $email = new AporteCorreo($data);
            $email->subject('Nuevo aporte | Arbolado Urbano');
            if ($request->hasFile('species-images')) {
                $images = $request->file('species-images');
                try {
                    foreach ($images as $index => $image) {
                        $email->attach($image->getRealPath(), ['as' => "imagen_$index", 'mime' => $image->getMimeType()]);
                    }
                } catch (\Throwable $th) {
                    \Log::error('Nuevo aporte - error adjuntando fotos para email:');
                    \Log::error($th);
                }
            }
            try {
                Mail::to(config('mail.admin_email'))->send($email);
            } catch (\Throwable $th) {
                \Log::error('Nuevo aporte - error al enviar email a admin:');
                \Log::error($th);
            }

            // Email usuario
            $emailConfirmacion = new AporteConfirmacionCorreo($data);
            $emailConfirmacion->subject('Nuevo aporte | Arbolado Urbano');
            try {
                Mail::to($data['email'])->send($emailConfirmacion);
            } catch (\Throwable $th) {
                \Log::error('Nuevo árbol - error al enviar email a usuario:');
                \Log::error($th);
            }

            return response('', 200);
        } catch (\Throwable $th) {
            \Log::error('Nuevo aporte - error al crear nuevo aporte:');
            \Log::error($th);
            return response('', 500);
        }
    }
}
