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

    public function sendMessage(string $message, ?string $chatId = null): void
    {
        $chatId = $chatId ?: $this->groupId;
        if (empty($chatId)) {
            \Log::error('Telegram chat ID is not set. Message not sent.', [
                'message' => $message
            ]);
            return;
        }

        try {
            Http::timeout(10)
                ->async()
                ->post("{$this->baseUrl}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ])
                ->then(function ($response) use ($chatId, $message) {
                    if (!$response->successful()) {
                        \Log::error('Telegram send message failed', [
                            'status' => $response->status(),
                            'response' => $response->body(),
                            'chat_id' => $chatId,
                            'message' => $message
                        ]);
                    }
                });
        } catch (\Exception $e) {
            \Log::error('Telegram send message exception', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'message' => $message
            ]);
        }
    }
}
