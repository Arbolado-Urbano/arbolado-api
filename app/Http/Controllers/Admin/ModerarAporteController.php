<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aporte;
use App\Models\Arbol;
use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ModerarAporteController extends Controller
{
    public function aprobar(Request $request, int $id)
    {
        $datos = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'especie_id' => 'required|integer|exists:especies,id',
            'altura' => 'nullable|numeric|min:0',
            'diametro_a_p' => 'nullable|numeric|min:0',
            'diametro_copa' => 'nullable|numeric|min:0',
            'inclinacion' => 'nullable|string|max:255',
            'estado_fitosanitario' => 'nullable|string|max:255',
            'etapa_desarrollo' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
        ]);

        $arbolId = DB::transaction(function () use ($id, $datos) {
            $aporte = Aporte::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($aporte->cargado !== null) {
                throw ValidationException::withMessages(['aporte' => 'El aporte ya fue procesado.']);
            }
            $arbol = Arbol::create([
                'lat' => $datos['lat'],
                'lng' => $datos['lng'],
                'especie_id' => $datos['especie_id'],
            ]);
            Registro::create([
                'arbol_id' => $arbol->id,
                'fuente_id' => $aporte->fuente_id,
                'altura' => $datos['altura'] ?? null,
                'diametro_a_p' => $datos['diametro_a_p'] ?? null,
                'diametro_copa' => $datos['diametro_copa'] ?? null,
                'inclinacion' => $datos['inclinacion'] ?? null,
                'estado_fitosanitario' => $datos['estado_fitosanitario'] ?? null,
                'etapa_desarrollo' => $datos['etapa_desarrollo'] ?? null,
                'notas' => $datos['notas'] ?? null,
            ]);
            DB::table('aportes_notificaciones')->insert([ 'aporte_id' => $aporte->id, 'fuente_id' => $aporte->fuente_id, 'arbol_id' => $arbol->id, 'estado' => 'aprobado', 'created_at' => now(), 'updated_at' => now() ]);
            $aporte->cargado = 1;
            $aporte->save();
            return $arbol->id;
        });
        return response()->json(['estado' => 'aprobado', 'arbol_id' => $arbolId]);
    }

    public function rechazar(int $id)
    {
        DB::transaction(function () use ($id) {
            $aporte = Aporte::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($aporte->cargado !== null) {
                throw ValidationException::withMessages(['aporte' => 'El aporte ya fue procesado.']);
            }
            DB::table('aportes_notificaciones')->insert([ 'aporte_id' => $aporte->id, 'fuente_id' => $aporte->fuente_id, 'arbol_id' => null, 'estado' => 'rechazado', 'created_at' => now(), 'updated_at' => now() ]);
            $aporte->cargado = 2;
            $aporte->save();
        });
        return response()->json(['estado' => 'rechazado']);
    }
}
