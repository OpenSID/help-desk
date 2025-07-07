<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    protected string $botToken;
    protected string $groupId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token_telegram');
        $this->groupId = config('services.telegram.group_id_telegram');
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    public function sendMessage(string $message, ?string $chatId = null): bool
    {
        $chatId = $chatId ?: $this->groupId;
        $response = Http::withOptions([
                'verify' => false,
            ])
            ->timeout(10)
            ->post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

        return $response->successful();
    }
}
