<?php

namespace App\Http\Controllers;

use App\Models\Usuario;

use App\Rules\CaptchaRule;

use Illuminate\Http\Request;

class UsuariosController extends Controller
{
    /**
     * Obtener la fuente a la que pertenece un usuario
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response La fuente si el código fue encontrado, 404 si no existe, 422 si la validación de captcha falla
     */
    public function get(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string',
            'captcha'   => ['required', new CaptchaRule()],
        ]);
        $user = Usuario::where('usuarios.codigo', $data['code'])->with('source')->first();
        if (!$user) {
            abort(404);
        }
        return response()->json([
            'slug' => $user->source->slug,
            'lat' => $user->source->lat,
            'lng' => $user->source->lng,
        ]);
    }
}
