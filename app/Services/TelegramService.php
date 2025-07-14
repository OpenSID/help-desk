<?php

/**
 * TelegramService
 *
 * Service untuk mengirim pesan notifikasi ke Telegram menggunakan Bot API.
 * - Mengambil konfigurasi token bot dan group ID dari file konfigurasi.
 * - Mendukung pengiriman pesan ke grup atau user tertentu.
 * - Logging error jika pengiriman gagal.
 *
 * @package App\Services
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    /**
     * Token bot Telegram yang digunakan untuk autentikasi API.
     *
     * @var string
     */
    protected string $botToken;

    /**
     * Default group ID Telegram untuk pengiriman pesan.
     *
     * @var string
     */
    protected string $groupId;

    /**
     * Base URL endpoint API Telegram.
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * Konstruktor.
     * Menginisialisasi token bot, group ID, dan base URL dari konfigurasi aplikasi.
     */
    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token_telegram');
        $this->groupId = config('services.telegram.group_id_telegram');
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Mengirim pesan ke Telegram.
     *
     * @param string $message Pesan yang akan dikirim (format HTML didukung).
     * @param string|null $chatId ID chat tujuan (user atau grup). Jika null, akan menggunakan default group ID.
     * @return void
     *
     * - Jika chatId tidak diisi, pesan dikirim ke grup default.
     * - Jika gagal, error dicatat ke log aplikasi.
     * - Menggunakan request async agar tidak blocking proses utama.
     */
    public function sendMessage(string $message, ?string $chatId = null): void
    {
        $chatId = $chatId ?: $this->groupId;

        try {
            Http::timeout(10)
                ->async()
                ->post("{$this->baseUrl}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ])
                ->then(function ($response) use ($chatId, $message) {
                    // Logging jika response gagal
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
            // Logging jika terjadi exception saat request
            \Log::error('Telegram send message exception', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'message' => $message
            ]);
        }
    }
}
