<?php

namespace App\Http\Controllers;

use App\Models\Usuario;

use App\Services\CaptchaService;

use Illuminate\Http\Request;

class UsuariosController extends Controller
{

    /**
   * Valida la existencia o no de un código de usuario
   *
   * @param  \Illuminate\Http\Request $request
   * @param  App\Services\CaptchaService $captchaService
   * @param  string $code - El código del usuario
   * @return \Illuminate\Http\Response 204 si el código fue encontrado, 404 si no existe, 422 si la validación de captcha falla
   */
    public function codeExists(Request $request, CaptchaService $captchaService, string $code)
    {
        $data = $request->validate(['captcha' => 'required|string']);
        if (!$code || !$captchaService->verify($data['captcha'])) {
            abort(422);
        }
        if (!Usuario::where('usuarios.codigo', $code)->exists()) {
            abort(404);
        }
        return response()->noContent();
    }
}
