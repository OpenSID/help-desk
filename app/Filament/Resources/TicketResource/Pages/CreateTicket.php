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
     * @var TelegramService
     */
    protected TelegramService $telegram;

    /**
     * Konstruktor.
     * Menginisialisasi service Telegram dari service container.
     *
     * @param mixed $id
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->telegram = app(TelegramService::class); // Ambil service dari container Laravel
    }

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

    /**
     * Proses setelah tiket berhasil dibuat.
     * - Mengirim pesan notifikasi ke Telegram.
     * - Menyimpan relasi kategori tiket (many-to-many) ke tabel pivot.
     *
     * @return void
     */
    protected function afterCreate(): void
    {
        $ticket = $this->record;
        $title = htmlspecialchars($ticket->name, ENT_QUOTES, 'UTF-8');
        $owner = htmlspecialchars(optional($ticket->owner)->name ?? 'Tidak diketahui', ENT_QUOTES, 'UTF-8');
        $assignee = optional($ticket->responsible)->telegram_id ?? null;
        $mention = htmlspecialchars(optional($ticket->responsible)->name ?? 'Tidak diketahui', ENT_QUOTES, 'UTF-8');
        $createdAt = htmlspecialchars($ticket->created_at->format('d M Y H:i'), ENT_QUOTES, 'UTF-8');
        $content = htmlspecialchars(Str::limit(strip_tags($ticket->content), 50, '...'), ENT_QUOTES, 'UTF-8');
        $link = htmlspecialchars(route('filament.resources.tickets.view', $ticket->id), ENT_QUOTES, 'UTF-8');

        // Susun pesan notifikasi Telegram
        $message = "🆕 <b>Tiket Baru Dibuat</b>\n"
            . "📄 Judul: <b>{$title}</b>\n"
            . "🙋‍♂️ Dari: {$owner}\n"
            . "👤 Kepada: {$mention}\n"
            . "📅 Tanggal: {$createdAt}\n"
            . "📌 Deskripsi Singkat:\n"
            . "{$content}\n"
            . "🔗 <a href=\"{$link}\">Lihat Tiket</a>";

        // Kirim pesan ke Telegram
        $this->telegram->sendMessage($message, $assignee);

        // Sinkronisasi kategori tiket (many-to-many)
        $this->record->categories()->sync($this->categories);
    }

    protected function handleRecordCreation(array $data): Ticket
    {
        try {
            // Buat tiket di database
            $ticket = parent::handleRecordCreation($data);

            // Persiapkan data untuk GitHub
            $githubData = [
                'title' => $ticket->name,
                'body' => strip_tags($ticket->content),
                'assignees' => $ticket->responsible?->github_username ? [$ticket->responsible->github_username] : [],
                'labels' => [
                    'Helpdesk',
                    'type:' . ($ticket->type?->name ?? 'default'),
                    'status:' . ($ticket->status?->name ?? 'unknown'),
                ],
            ];

            // Inisialisasi GitHubService
            $github = app(\App\Services\GithubService::class);

            // Langkah 1: Buat issue di GitHub
            $response = $github->createIssue($githubData);

            if (!$response) {
                Log::error('Failed to create GitHub issue', ['ticket_id' => $ticket->id]);
                return $ticket; // Lanjutkan meskipun gagal, tapi tanpa data GitHub
            }

            // Simpan info GitHub ke database
            $ticket->github_issue_url = $response['html_url'] ?? null;
            $ticket->github_issue_number = $response['number'] ?? null;

            // Langkah 2: Tambahkan issue ke project board
            $projectItemId = null;
            if (!empty($response['node_id'])) {
                $projectItemId = $github->addToProject($response['node_id']);
                $ticket->github_project_item_id = $projectItemId;
            } else {
                Log::error('Missing node_id for adding to project', ['ticket_id' => $ticket->id]);
            }

            // Langkah 3: Update field custom seperti Status dan Ticket Authors
            if ($projectItemId) {
                $statusFieldId = $github->getProjectFieldId('Status');
                $ticketAuthorFieldId = $github->getProjectFieldId('Ticket Authors');

                $fieldValues = [];

                if ($statusFieldId) {
                    $statusOptionId = $github->getSingleSelectOptionId($statusFieldId, $ticket->status?->name ?? 'unknown');
                    if ($statusOptionId) {
                        $fieldValues[] = [
                            'fieldId' => $statusFieldId,
                            'value' => ['singleSelectOptionId' => $statusOptionId],
                        ];
                    } else {
                        Log::warning('Status option not found', [
                            'status' => $ticket->status?->name ?? 'unknown',
                            'ticket_id' => $ticket->id,
                        ]);
                    }
                } else {
                    Log::warning('Status field ID not found', ['ticket_id' => $ticket->id]);
                }

                if ($ticketAuthorFieldId) {
                    $authorOptionId = $github->getSingleSelectOptionId($ticketAuthorFieldId, $ticket->owner?->name ?? 'unknown');
                    if ($authorOptionId) {
                        $fieldValues[] = [
                            'fieldId' => $ticketAuthorFieldId,
                            'value' => ['singleSelectOptionId' => $authorOptionId],
                        ];
                    } else {
                        Log::warning('Author option not found', [
                            'author' => $ticket->owner?->name ?? 'unknown',
                            'ticket_id' => $ticket->id,
                        ]);
                    }
                } else {
                    Log::warning('Ticket Authors field ID not found', ['ticket_id' => $ticket->id]);
                }

                if (!empty($fieldValues)) {
                    // Update field custom
                    $github->updateProjectFields($projectItemId, $fieldValues);
                }
            } else {
                Log::warning('Project item ID not found, skipping field updates', ['ticket_id' => $ticket->id]);
            }

            // Simpan perubahan ke database
            $ticket->save();

            return $ticket;
        } catch (\Exception $e) {
            Log::error('Error creating GitHub issue: ' . $e->getMessage(), ['ticket_id' => $ticket->id]);
            throw $e;
        }
    }

}
