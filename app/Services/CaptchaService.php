<?php

namespace App\Services;

class CaptchaService implements CaptchaServiceInterface
{
    public function generateCaptcha(): array
    {
        $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        $expiredAt = now()->addMinutes(5);

        return [
            'captcha_code' => $code,
            'expired_at' => $expiredAt,
        ];
    }

    public function validateCaptcha(string $input, string $captchaCode): bool
    {
        return strtoupper($input) === strtoupper($captchaCode);
    }
}
