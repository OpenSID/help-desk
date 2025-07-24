<?php

namespace App\Services;

interface CaptchaServiceInterface
{
    public function generateCaptcha(): array;
    public function validateCaptcha(string $input, string $captchaCode): bool;
}
