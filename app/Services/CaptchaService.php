<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CaptchaService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private string $secret) {}

    public function verify(string $captcha): bool
    {
        try {
            $res = Http::post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret'   => $this->secret,
                'response' => $captcha,
            ])->json();
            return $res['success'] ?? false;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
