<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

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
