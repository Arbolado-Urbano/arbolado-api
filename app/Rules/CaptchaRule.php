<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class CaptchaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string   $attribute  The name of the attribute being validated.
     * @param  mixed    $value      The value of the attribute.
     * @param  Closure  $fail       Call with a message to indicate validation failure.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $res = Http::post(config('services.captcha.url'), [
                'secret'   => config('services.captcha.secret'),
                'response' => $value,
            ])->json();
            if (!($res['success'] ?? false)) {
                $fail('Captcha verification failed.');
            }
        } catch (\Throwable $th) {
            \Log::error('Captcha verification - error al verificar captcha:');
            \Log::error($th);
            $fail('Captcha verification failed.');
        }
    }
}
