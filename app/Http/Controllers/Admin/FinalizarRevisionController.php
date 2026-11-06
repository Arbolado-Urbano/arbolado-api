<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AportePublicado;
use App\Models\Aporte;
use App\Models\Arbol;
use App\Models\Fuente;
use App\Models\Registro;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class FinalizarRevisionController extends Controller
{
    private function datos(int $fuenteId): array
    {
        $fuente = Fuente::findOrFail($fuenteId);
        $aportes = Aporte::where('fuente_id', $fuenteId)->whereIn('cargado', [-1, -2])->orderBy('id')->get();
        $aprobados = $aportes->where('cargado', -1)->count();
        $rechazados = $aportes->where('cargado', -2)->count();
        return compact('fuente', 'aportes', 'aprobados', 'rechazados');
    }

    public function preview(int $fuenteId): JsonResponse
    {
        $d = $this->datos($fuenteId);
        return response()->json([
            'fuente_id' => $fuenteId,
            'nombre' => $d['fuente']->nombre,
            'tiene_email' => filter_var($d['fuente']->email, FILTER_VALIDATE_EMAIL) !== false,
            'aprobados' => $d['aprobados'],
            'rechazados' => $d['rechazados'],
            'ids' => $d['aportes']->pluck('id'),
            'puede_finalizar' => $d['aportes']->isNotEmpty(),
        ]);
    }

    public function finalizar(int $fuenteId): JsonResponse
    {
        // Bloqueo por colaborador: impide dos cierres simultáneos para la misma fuente.
        $resultado = DB::transaction(function () use ($fuenteId) {
            $fuente = Fuente::whereKey($fuenteId)->lockForUpdate()->firstOrFail();
            $aportes = Aporte::where('fuente_id', $fuenteId)
                ->whereIn('cargado', [-1, -2])->orderBy('id')->lockForUpdate()->get();

            if ($aportes->isEmpty()) {
                throw ValidationException::withMessages(['aportes' => 'No hay decisiones pendientes de notificar.']);
            }
            if (!filter_var($fuente->email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['email' => 'El colaborador no tiene un correo válido.']);
            }

            $aprobados = $aportes->where('cargado', -1)->count();
            $rechazados = $aportes->where('cargado', -2)->count();
            $urlPlantilla = config('services.arbolado_mapa_fuente_url');
            if ($aprobados && (!$fuente->slug || !is_string($urlPlantilla) || !str_contains($urlPlantilla, '{slug}'))) {
                throw ValidationException::withMessages(['url_mapa' => 'Configurá ARBOLADO_MAPA_FUENTE_URL con {slug} antes de finalizar.']);
            }
            $urlMapa = $aprobados ? str_replace('{slug}', rawurlencode($fuente->slug), $urlPlantilla) : null;
            if ($urlMapa && (!filter_var($urlMapa, FILTER_VALIDATE_URL) || !in_array(parse_url($urlMapa, PHP_URL_HOST), ['arboladourbano.com', 'www.arboladourbano.com'], true))) {
                throw ValidationException::withMessages(['url_mapa' => 'La URL del mapa del colaborador no es válida.']);
            }

            // La transacción se revierte si falla el transporte de correo.
            // El envío y la confirmación SQL no son atómicos ante fallas externas.
            Mail::to($fuente->email)->send(new AportePublicado([
                'nombre' => $fuente->nombre,
                'cantidad' => $aprobados,
                'rechazados' => $rechazados,
                'url_mapa' => $urlMapa,
            ]));

            foreach ($aportes as $aporte) {
                if ((int) $aporte->cargado === -1) {
                    $arbol = Arbol::create([
                        'lat' => $aporte->lat,
                        'lng' => $aporte->lng,
                        'especie_id' => $aporte->especie_id,
                    ]);
                    Registro::create([
                        'arbol_id' => $arbol->id,
                        'fuente_id' => $aporte->fuente_id,
                        'altura' => $aporte->altura,
                        'diametro_a_p' => $aporte->diametro_a_p,
                        'diametro_copa' => $aporte->diametro_copa,
                        'inclinacion' => $aporte->inclinacion,
                        'estado_fitosanitario' => $aporte->estado_fitosanitario,
                        'etapa_desarrollo' => $aporte->etapa_desarrollo,
                        'notas' => $aporte->notas,
                    ]);
                    $aporte->cargado = 1;
                } else {
                    $aporte->cargado = 2;
                }
                $aporte->save();
            }

            return ['aprobados' => $aprobados, 'rechazados' => $rechazados, 'total' => $aportes->count()];
        });

        return response()->json(['estado' => 'finalizado', ...$resultado]);
    }
}
