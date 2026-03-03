<?php
/**
 * EditTicketClassification
 *
 * Halaman untuk mengedit klasifikasi tiket pada resource TicketClassificationResource.
 *
 * @package App\Filament\Resources\TicketClassificationResource\Pages
 */

namespace App\Filament\Resources\TicketClassificationResource\Pages;

use App\Filament\Resources\TicketClassificationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicketClassification extends EditRecord
{
    /**
     * Resource yang terkait dengan halaman ini.
     *
     * @var string
     */
    protected static string $resource = TicketClassificationResource::class;

    /**
     * Mendapatkan judul halaman edit klasifikasi tiket.
     *
     * @return string Judul halaman (diterjemahkan)
     */
    public function getTitle(): string
    {
        return __('Edit Classification');
    }

    /**
     * Mendapatkan daftar aksi yang tersedia pada halaman edit.
     *
     * @return array Daftar aksi (misal: hapus)
     */
    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
