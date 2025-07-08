<?php
/**
 * ListTicketCategories
 *
 * Halaman untuk menampilkan daftar kategori tiket pada resource TicketCategoryResource.
 *
 * @package App\Filament\Resources\TicketCategoryResource\Pages
 */

namespace App\Filament\Resources\TicketCategoryResource\Pages;

use App\Filament\Resources\TicketCategoryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketCategories extends ListRecords
{
    /**
     * Resource yang terkait dengan halaman ini.
     *
     * @var string
     */
    protected static string $resource = TicketCategoryResource::class;

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
