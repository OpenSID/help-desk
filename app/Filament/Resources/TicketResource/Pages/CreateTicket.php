<?php

/**
 * CreateTicket
 *
 * Halaman untuk membuat tiket baru pada resource TicketResource.
 * - Mengelola input kategori tiket (many-to-many).
 * - Mengirim notifikasi Telegram setelah tiket dibuat.
 * - Menyimpan relasi kategori ke tiket.
 *
 * @package App\Filament\Resources\TicketResource\Pages
 */

namespace App\Filament\Resources\TicketResource\Pages;

use App\Models\Ticket;
use Illuminate\Support\Str;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;
use League\HTMLToMarkdown\HtmlConverter;
use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    /**
     * Resource yang terkait dengan halaman ini.
     *
     * @var string
     */
    protected static string $resource = TicketResource::class;

    /**
     * Menyimpan daftar kategori yang dipilih pada form.
     *
     * @var array
     */
    protected array $categories = [];

    /**
     * Service untuk mengirim notifikasi Telegram.
     *
     * @var TelegramService|null
     */
    protected ?TelegramService $telegram = null;

    /**
     * Konstruktor.
     * Menginisialisasi service Telegram dari service container.
     *
     * @param mixed $id
     */
    public ?int $id = null;

    public function mount($id = null): void
    {
        parent::mount($id);
        $this->id = $id;

        // Inisialisasi TelegramService
        try {
            $this->telegram = app(TelegramService::class);
            Log::info('TelegramService berhasil diinisialisasi di CreateTicket');
        } catch (\Exception $e) {
            Log::error('Gagal menginisialisasi TelegramService: ' . $e->getMessage());
            $this->telegram = null;
        }
    }
    // public function __construct($id = null)
    // {
    //     parent::__construct($id);
    //     $this->telegram = app(TelegramService::class); // Ambil service dari container Laravel
    // }

    /**
     * Memodifikasi data form sebelum proses create.
     * - Menyimpan data kategori ke properti $categories.
     * - Menghapus field 'categories' dari data yang akan disimpan ke tabel tiket.
     *
     * @param array $data Data form input
     * @return array Data yang sudah dimodifikasi (tanpa field 'categories')
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->categories = $data['categories'] ?? [];
        unset($data['categories']);
        return $data;
    }

    protected function handleRecordCreation(array $data): Ticket
    {
        try {
            // Buat tiket di database
            $ticket = parent::handleRecordCreation($data);

            Log::info('[CreateTicket] Ticket created, starting post-creation process', [
                'ticket_id' => $ticket->id,
            ]);

            // === BAGIAN 1: KIRIM NOTIFIKASI TELEGRAM ===
            $this->sendTelegramNotification($ticket);

            // === BAGIAN 2: SINKRONISASI KATEGORI ===
            if (!empty($this->categories)) {
                $ticket->categories()->sync($this->categories);
                Log::info('[CreateTicket] Categories synced', [
                    'ticket_id' => $ticket->id,
                    'categories' => $this->categories,
                ]);
            }

            // === BAGIAN 3: KIRIM KE GITHUB ===
            // Ambil instance HtmlConverter dari container
            $converter = app(HtmlConverter::class);

            // Bersihkan HTML: ganti <br> berturut-turut dengan satu <br>
            $cleanedHtml = preg_replace('/<br\s*\/?>\s*<br\s*\/?>/i', '<br>', $ticket->content ?? '');

            $markdownContent = $converter->convert($cleanedHtml);

            // Hilangkan backslash dari URL (misal: \_ menjadi _)
            $markdownContent = str_replace(['\_', '\*', '\[', '\]'], ['_', '*', '[', ']'], $markdownContent);

            // Persiapkan data untuk GitHub
            $labels = [
                'Helpdesk',
                $ticket->type?->name,
                $ticket->project?->name,
            ];

            $labelColors = [
                'Helpdesk' => '0000FF', // Biru untuk label Helpdesk
                $ticket->type?->name => $ticket->type?->color ?? 'D3D3D3',
                $ticket->project?->name => 'D3D3D3',
            ];

            $githubData = [
                'title' => $ticket->name,
                'body' => $markdownContent,
                'assignees' => $ticket->responsible?->github_username ? [$ticket->responsible->github_username] : [],
                'labels' => $labels,
                'label_colors' => $labelColors,
                'ticket_id' => $ticket->id, // Pastikan ini ada
            ];

            // Log data untuk debugging
            Log::info('[CreateTicket] Dispatching ProcessGitHubTicket job', [
                'ticket_id' => $ticket->id,
                'github_data' => $githubData,
            ]);

            // Dispatch ke queue untuk menghindari rate limit
            \App\Jobs\ProcessGitHubTicket::dispatch('create', $githubData);

            // Simpan perubahan ke database
            $ticket->save();

            return $ticket;
        } catch (\Exception $e) {
            Log::error('[CreateTicket] Error in handleRecordCreation: ' . $e->getMessage(), [
                'ticket_id' => $ticket->id ?? 'unknown',
            ]);
            throw $e;
        }
    }

    /**
     * Mengirim notifikasi Telegram ke user yang ditugaskan.
     *
     * @param Ticket $ticket
     * @return void
     */
    protected function sendTelegramNotification(Ticket $ticket): void
    {
        $assignee = optional($ticket->responsible)->telegram_id ?? null;

        if (!$this->telegram) {
            Log::warning('[CreateTicket] TelegramService tidak tersedia', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        if (!$assignee) {
            Log::info('[CreateTicket] Notifikasi Telegram tidak dikirim: assignee tidak memiliki telegram_id', [
                'ticket_id' => $ticket->id,
                'responsible_id' => $ticket->responsible_id,
            ]);
            return;
        }

        try {
            $title = htmlspecialchars($ticket->name, ENT_QUOTES, 'UTF-8');
            $owner = htmlspecialchars(optional($ticket->owner)->name ?? 'Tidak diketahui', ENT_QUOTES, 'UTF-8');
            $mention = htmlspecialchars(optional($ticket->responsible)->name ?? 'Tidak diketahui', ENT_QUOTES, 'UTF-8');
            $createdAt = htmlspecialchars($ticket->created_at->format('d M Y H:i'), ENT_QUOTES, 'UTF-8');
            $content = htmlspecialchars(Str::limit(strip_tags($ticket->content), 50, '...'), ENT_QUOTES, 'UTF-8');

            // Generate ticket URL menggunakan Filament Resource getUrl
            $ticketUrl = \App\Filament\Resources\TicketResource::getUrl('view', ['record' => $ticket->id]);
            $link = htmlspecialchars($ticketUrl, ENT_QUOTES, 'UTF-8');

            // Susun pesan notifikasi Telegram
            $message = "🆕 <b>Tiket Baru Dibuat</b>\n"
                . "📄 Judul: <b>{$title}</b>\n"
                . "🙋‍♂️ Dari: {$owner}\n"
                . "👤 Kepada: {$mention}\n"
                . "📅 Tanggal: {$createdAt}\n"
                . "📌 Deskripsi Singkat:\n"
                . "{$content}\n"
                . "🔗 <a href=\"{$link}\">Lihat Tiket</a>";

            $this->telegram->sendMessage($message, $assignee);

            Log::info('[CreateTicket] Notifikasi Telegram berhasil dikirim', [
                'ticket_id' => $ticket->id,
                'assignee' => $assignee,
            ]);
        } catch (\Exception $e) {
            Log::error('[CreateTicket] Gagal mengirim notifikasi Telegram: ' . $e->getMessage(), [
                'ticket_id' => $ticket->id,
                'assignee' => $assignee,
            ]);
        }
    }

}
