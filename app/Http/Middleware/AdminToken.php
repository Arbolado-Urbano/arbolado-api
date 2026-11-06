<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('services.admin.token');

        $providedToken = $request->bearerToken();

        if (
            !is_string($expectedToken) ||
            $expectedToken === '' ||
            !is_string($providedToken) ||
            !hash_equals($expectedToken, $providedToken)
        ) {
            return response()->json([
                'message' => 'Acceso no autorizado'
            ], 401);
        }

        return $next($request);
    }
}
