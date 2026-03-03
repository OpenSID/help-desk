<?php
/**
 * CreateTicketClassification
 *
 * Halaman untuk membuat klasifikasi tiket baru pada resource TicketClassificationResource.
 *
 * @package App\Filament\Resources\TicketClassificationResource\Pages
 */

namespace App\Filament\Resources\TicketClassificationResource\Pages;

use App\Filament\Resources\TicketClassificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketClassification extends CreateRecord
{
    /**
     * Resource yang terkait dengan halaman ini.
     *
     * @var string
     */
    protected static string $resource = TicketClassificationResource::class;

    /**
     * Mendapatkan judul halaman create klasifikasi tiket.
     *
     * @return string Judul halaman (diterjemahkan)
     */
    public function getTitle(): string
    {
        return __('Create Classification');
    }
}
