<?php

namespace App\Http\Controllers;

use App\Models\Usuario;

use App\Services\CaptchaService;

use Illuminate\Http\Request;

class UsuariosController extends Controller
{

    /**
   * Retorna la fuente a la que pertenece un usuario
   *
   * @param  \Illuminate\Http\Request $request
   * @param  App\Services\CaptchaService $captchaService
   * @return \Illuminate\Http\Response La fuente si el código fue encontrado, 404 si no existe, 422 si la validación de captcha falla
   */
    public function get(Request $request, CaptchaService $captchaService)
    {
        $data = $request->validate(['captcha' => 'required|string', 'code' => 'required|string']);
        if (!$captchaService->verify($data['captcha'])) {
            abort(422);
        }
        $user = Usuario::where('usuarios.codigo', $data['code'])->with('source')->first();
        if (!$user) {
            abort(404);
        }
        return response($user->source->slug);
    }
}
