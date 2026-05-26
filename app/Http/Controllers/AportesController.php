<?php

namespace App\Http\Controllers;

use App\Models\Fuente;
use App\Models\Aporte;
use App\Models\Especie;

use App\Mail\Aporte as AporteCorreo;
use App\Mail\AporteConfirmacion as AporteConfirmacionCorreo;

use App\Rules\CaptchaRule;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AportesController extends Controller
{
    /**
     * Agregar un nuevo aporte
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        $data = $request->validate([
            'email'          => 'required|email',
            'name'           => 'required|string',
            'coordinates'    => ['required', 'regex:/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/'],
            'species'        => 'nullable|required_without:speciesId',
            'speciesId'      => 'nullable|integer|required_without:species',
            'captcha'        => ['required', new CaptchaRule()],
            'website'        => 'nullable|string',
            'height'         => 'nullable|string',
            'diameterTrunk'  => 'nullable|string',
            'diameterCanopy' => 'nullable|string',
            'inclination'    => 'nullable|string',
            'health'         => 'nullable|string',
            'development'    => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($data) {
                $latLng = explode(',', $data['coordinates']);
                Fuente::upsert([
                    [
                        'nombre' => $data['name'],
                        'email' => $data['email'],
                        'url' => $data['website'] ?? null,
                    ],
                ], uniqueBy: ['email'], update: ['nombre', 'url']);
                $fuenteId = Fuente::where('email', $data['email'])->first()->id;
                Aporte::create([
                    'lat' => $latLng[0],
                    'lng' => $latLng[1],
                    'especie' => $data['species'] ?? null,
                    'altura' => $data['height'] ?? null,
                    'diametro_a_p' => $data['diameterTrunk'] ?? null,
                    'diametro_copa' => $data['diameterCanopy'] ?? null,
                    'inclinacion' => $data['inclination'] ?? null,
                    'estado_fitosanitario' => $data['health'] ?? null,
                    'etapa_desarrollo' => $data['development'] ?? null,
                    'fuente_id' => $fuenteId,
                    'especie_id' => $data['speciesId'] ?? null,
                    'notas' => $data['notes'] ?? null,
                ]);
            });

            // Por el chequeo inicial si "speciesId" no está definido entonces "species" si está definido y vice-versa.
            $speciesId = $data['speciesId'] ?? null;
            if ($speciesId) {
                $especie = Especie::select(['nombre_cientifico', 'nombre_comun'])->where('id', $speciesId)->first();
                $data['species'] = $especie->nombre_comun ? $especie->nombre_comun . ' (' . $especie->nombre_cientifico . ')' : $especie->nombre_cientifico;
            }
            
            // Email admin
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

            return response()->json();
        } catch (\Throwable $th) {
            \Log::error('Nuevo aporte - error al crear nuevo aporte:');
            \Log::error($th);
            abort(500);
        }
    }
}
