<?php

namespace App\Providers;

use App\Services\CaptchaService;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Client\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CaptchaService::class, function () {
            return new CaptchaService(config('services.captcha.secret'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('validJson', function ($key = null, $default = null) {
            // json_decode with JSON_THROW_ON_ERROR will throw on invalid JSON
            // then delegate to the built-in json() for key/default handling
            json_decode($this->body(), true, 512, JSON_THROW_ON_ERROR);
            return $this->json($key, $default);
        });
    }
}
