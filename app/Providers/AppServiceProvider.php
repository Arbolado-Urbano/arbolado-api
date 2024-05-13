<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \Illuminate\Http\Client\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Same as ->json() but it will throw if parsing fails
        Response::macro('validJson', function ($key = null, $default = null) {
            if (! $this->decoded) {
                $this->decoded = json_decode($this->body(), true, 512, JSON_THROW_ON_ERROR);
            }
            return $this->json($key, $default);
        });
    }
}
