<?php

namespace App\Http\Middleware;

use Closure;

class CORSOptions
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
            return $next($request)
              ->header('Access-Control-Allow-Origin', $_SERVER['HTTP_ORIGIN'])
              ->header('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE')
              ->header('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, X-CSRF-TOKEN')
              ->header('Access-Control-Allow-Credentials', 'true');
        } catch (\Exception $e) {
            var_dump($e);
        }
    }
}
