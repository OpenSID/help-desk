<?php
/**
 * EditTicketCategory
 *
 * Halaman untuk mengedit kategori tiket pada resource TicketCategoryResource.
 *
 * @package App\Filament\Resources\TicketCategoryResource\Pages
 */

namespace App\Filament\Resources\TicketCategoryResource\Pages;

use App\Filament\Resources\TicketCategoryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicketCategory extends EditRecord
{
    /**
     * Resource yang terkait dengan halaman ini.
     *
     * @var string
     */
    protected static string $resource = TicketCategoryResource::class;

    /**
     * Mendapatkan judul halaman edit kategori tiket.
     *
     * @return string Judul halaman (diterjemahkan)
     */
    public function getTitle(): string
    {
        return __('Edit Solution Categories');
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
