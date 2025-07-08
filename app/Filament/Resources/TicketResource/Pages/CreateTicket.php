<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected array $categories = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->categories = $data['categories'] ?? [];
        unset($data['categories']);
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->categories()->sync($this->categories);
    }
}
