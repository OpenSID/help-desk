<?php
/**
 * ListTicketClassifications
 *
 * Halaman untuk menampilkan daftar klasifikasi tiket pada resource TicketClassificationResource.
 *
 * @package App\Filament\Resources\TicketClassificationResource\Pages
 */

namespace App\Filament\Resources\TicketClassificationResource\Pages;

use App\Filament\Resources\TicketClassificationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketClassifications extends ListRecords
{
    /**
     * Resource yang terkait dengan halaman ini.
     *
     * @var string
     */
    protected static string $resource = TicketClassificationResource::class;

    /**
     * Mendapatkan daftar aksi yang tersedia pada halaman list.
     *
     * @return array Daftar aksi (misal: tambah data)
     */
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
