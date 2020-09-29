<?php

namespace App\Http\Middleware;

use Closure;

class CORSOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (isset($_SERVER['HTTP_ORIGIN'])) {
                return $next($request)
                ->header('Access-Control-Allow-Origin', $_SERVER['HTTP_ORIGIN']);
            }

            return $next($request);
        } catch (\Exception $e) {
            var_dump($e);
        }
    }
}
