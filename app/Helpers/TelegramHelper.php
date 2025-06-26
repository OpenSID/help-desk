<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class Telegram
{
    public static function sendMessage($message)
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        return Http::withOptions([
            'verify' => false,
            ])->timeout(10)->post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
    }
}
