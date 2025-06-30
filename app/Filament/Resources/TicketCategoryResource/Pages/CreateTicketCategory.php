<?php

namespace App\Filament\Resources\TicketCategoryResource\Pages;

use App\Filament\Resources\TicketCategoryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketCategory extends CreateRecord
{
    protected static string $resource = TicketCategoryResource::class;

    protected function getTitle(): string
    {
        return __('Crete Solution Categories');
    }
}
