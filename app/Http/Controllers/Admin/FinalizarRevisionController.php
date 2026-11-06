<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AportePublicado;
use App\Models\Aporte;
use App\Models\Arbol;
use App\Models\Fuente;
use App\Models\Registro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function finalizar(Request $request, int $fuenteId): JsonResponse
    {
        // Esta fase está restringida a pruebas locales y correo en log.
        if (!app()->environment('local') || config('mail.default') !== 'log') {
            return response()->json(['message' => 'Finalización habilitada únicamente en entorno local con MAIL_MAILER=log.'], 403);
        }

        $texto = $request->validate([
            'asunto' => ['required', 'string', 'max:200'],
            'mensaje' => ['required', 'string', 'max:15000'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        // Primero publicar de forma atómica. El correo se procesa DESPUÉS del commit.
        // Una repetición nunca vuelve a crear árboles, porque los aportes dejan de estar en -1.
        $resultado = DB::transaction(function () use ($fuenteId, $texto) {
            $fuente = Fuente::whereKey($fuenteId)->lockForUpdate()->firstOrFail();
            $aportes = Aporte::where('fuente_id', $fuenteId)
                ->whereIn('cargado', [-1, -2])->orderBy('id')->lockForUpdate()->get();

            if ($aportes->isEmpty()) {
                throw ValidationException::withMessages(['aportes' => 'No hay decisiones pendientes de finalizar.']);
            }
            $esperados = $aportes->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $recibidos = collect($texto['ids'])->map(fn ($id) => (int) $id)->sort()->values()->all();
            if ($esperados !== $recibidos) {
                throw ValidationException::withMessages(['aportes' => 'Los aportes cambiaron desde la vista previa. Actualizá la revisión.']);
            }
            if (!filter_var($fuente->email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['email' => 'El colaborador no tiene un correo válido.']);
            }
            $aprobados = $aportes->where('cargado', -1)->count();
            $rechazados = $aportes->where('cargado', -2)->count();
            $urlMapa = 'https://arboladourbano.com/fuente/' . rawurlencode((string) $fuente->slug);
            if ($aprobados && !$fuente->slug) {
                throw ValidationException::withMessages(['url_mapa' => 'Falta el slug del colaborador.']);
            }

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
            return [
                'aprobados' => $aprobados,
                'rechazados' => $rechazados,
                'total' => $aportes->count(),
                'email' => $fuente->email,
                'nombre' => $fuente->nombre,
                'url_mapa' => $urlMapa,
            ];
        });

        try {
            Mail::to($resultado['email'])->send(new AportePublicado([
                'nombre' => $resultado['nombre'],
                'cantidad' => $resultado['aprobados'],
                'rechazados' => $resultado['rechazados'],
                'url_mapa' => $resultado['url_mapa'],
                'asunto' => $texto['asunto'],
                'mensaje' => $texto['mensaje'],
            ]));
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'estado' => 'publicado_sin_notificar',
                'message' => 'Los árboles se publicaron, pero falló el registro del correo. No vuelvas a finalizar: no se duplicarán los árboles.',
                'aprobados' => $resultado['aprobados'],
                'rechazados' => $resultado['rechazados'],
            ], 500);
        }

        return response()->json([
            'estado' => 'finalizado',
            'aprobados' => $resultado['aprobados'],
            'rechazados' => $resultado['rechazados'],
            'total' => $resultado['total'],
            'correo' => 'registrado_en_log',
        ]);
    }
}
