<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
// use App\Helpers\Telegram;
use Illuminate\Support\Str;
use App\Services\TelegramService;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected TelegramService $telegram;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->telegram = app(TelegramService::class); // Ambil service dari container Laravel
    }

    protected function afterCreate(): void
    {
        $ticket = $this->record;
        $title = htmlspecialchars($ticket->name, ENT_QUOTES, 'UTF-8');
        $owner = htmlspecialchars(optional($ticket->owner)->name ?? 'Tidak diketahui', ENT_QUOTES, 'UTF-8');
        $assignee = optional($ticket->responsible)->telegram_id ?? null;
        $mention = htmlspecialchars(optional($ticket->responsible)->name ?? 'Tidak diketahui', ENT_QUOTES, 'UTF-8');
        $createdAt = htmlspecialchars($ticket->created_at->format('d M Y H:i'), ENT_QUOTES, 'UTF-8');
        $content = htmlspecialchars(Str::limit(strip_tags($ticket->content), 25, '...'), ENT_QUOTES, 'UTF-8');
        $link = htmlspecialchars(route('filament.resources.tickets.view', $ticket->id), ENT_QUOTES, 'UTF-8');

        $message = "🆕 <b>Tiket Baru Dibuat</b>\n"
            . "📄 Judul: <b>{$title}</b>\n"
            . "🙋‍♂️ Dari: {$owner}\n"
            . "👤 Kepada: {$mention}\n"
            . "📅 Tanggal: {$createdAt}\n"
            . "📌 Deskripsi Singkat:\n"
            . "{$content}\n"
            . "🔗 <a href=\"{$link}\">Lihat Tiket</a>";

        $this->telegram->sendMessage($message, $assignee);
    }

    // protected function afterCreate(): void
    // {
    //     $ticket = $this->record;

    //     // Kirim pesan ke Telegram setelah tiket berhasil dibuat
    //     $title = htmlspecialchars($ticket->name, ENT_QUOTES, 'UTF-8');
    //     $owner = htmlspecialchars(optional($ticket->owner)->name ?? 'Tidak diketahui', ENT_QUOTES, 'UTF-8');
    //     $mention = $ticket->telegram_username
    //         ? '@' . preg_replace('/[^a-zA-Z0-9_]/', '', ltrim($ticket->telegram_username, '@'))
    //         : 'Tidak terdaftar';
    //     $mention = htmlspecialchars($mention, ENT_QUOTES, 'UTF-8');
    //     $createdAt = htmlspecialchars($ticket->created_at->format('d M Y H:i'), ENT_QUOTES, 'UTF-8');
    //     $content = htmlspecialchars(Str::limit(strip_tags($ticket->content), 25, '...'), ENT_QUOTES, 'UTF-8');
    //     $link = htmlspecialchars(route('filament.resources.tickets.view', $ticket->id), ENT_QUOTES, 'UTF-8');
    //     $message = "🆕 <b>Tiket Baru Dibuat</b>\n"
    //             . "📄 Judul: <b>{$title}</b>\n"
    //             . "🙋‍♂️ Dari: {$owner}\n"
    //             . "👤 Kepada: {$mention}\n"
    //             // . "🏷️ Kategori: {$ticket->user->name}\n" // menunggu di merge dengan kategori
    //             . "📅 Tanggal: {$createdAt}\n"
    //             . "📌 Deskripsi Singkat:\n"
    //             . "{$content}\n"
    //             . "🔗 <a href=\"{$link}\">Lihat Tiket</a>";
    //     Telegram::sendMessage($message);
    // }
}

