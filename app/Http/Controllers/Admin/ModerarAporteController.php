<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aporte;
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

        DB::transaction(function () use ($id, $datos) {
            $aporte = Aporte::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($aporte->cargado !== null && (int) $aporte->cargado !== -1 && (int) $aporte->cargado !== -2) {
                throw ValidationException::withMessages(['aporte' => 'El aporte ya fue finalizado.']);
            }
            $aporte->fill($datos);
            $aporte->cargado = -1;
            $aporte->save();
        });
        return response()->json(['estado' => 'aprobado_pendiente', 'cargado' => -1]);
    }

    public function rechazar(int $id)
    {
        DB::transaction(function () use ($id) {
            $aporte = Aporte::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($aporte->cargado !== null && (int) $aporte->cargado !== -1 && (int) $aporte->cargado !== -2) {
                throw ValidationException::withMessages(['aporte' => 'El aporte ya fue procesado.']);
            }
            $aporte->cargado = -2;
            $aporte->save();
        });
        return response()->json(['estado' => 'rechazado_pendiente', 'cargado' => -2]);
    }
}
