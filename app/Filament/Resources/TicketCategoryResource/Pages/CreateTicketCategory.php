<?php
/**
 * CreateTicketCategory
 *
 * Halaman untuk membuat kategori tiket baru pada resource TicketCategoryResource.
 *
 * @package App\Filament\Resources\TicketCategoryResource\Pages
 */

namespace App\Filament\Resources\TicketCategoryResource\Pages;

use App\Filament\Resources\TicketCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketCategory extends CreateRecord
{
    /**
     * Resource yang terkait dengan halaman ini.
     *
     * @var string
     */
    protected static string $resource = TicketCategoryResource::class;

    /**
     * Mendapatkan judul halaman create kategori tiket.
     *
     * @return string Judul halaman (diterjemahkan)
     */
    protected function getTitle(): string
    {
        return __('Crete Solution Categories');
    }
}
