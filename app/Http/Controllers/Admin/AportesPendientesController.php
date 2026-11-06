<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aporte;
use Illuminate\Http\JsonResponse;

class AportesPendientesController extends Controller
{
    public function index(): JsonResponse
    {
        $aportes = Aporte::query()
            ->with([
                'source:id,nombre,slug',
                'species'
            ])
            ->whereNull('cargado')
            ->orderBy('fuente_id')
            ->orderBy('id')
            ->get();

        $fuentes = $aportes
            ->groupBy('fuente_id')
            ->map(function ($grupo, $fuenteId) {
                $fuente = $grupo->first()->source;

                return [
                    'fuente_id' => (int) $fuenteId,
                    'nombre' => $fuente?->nombre,
                    'slug' => $fuente?->slug,
                    'cantidad_pendientes' => $grupo->count(),
                    'aportes' => $grupo->values(),
                ];
            })
            ->values();

        return response()->json([
            'total_pendientes' => $aportes->count(),
            'total_fuentes' => $fuentes->count(),
            'fuentes' => $fuentes,
        ]);
    }
}