<?php

namespace App\Filament\Resources\TokenResource\Pages;

use App\Filament\Resources\TokenResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditToken extends EditRecord
{
    protected static string $resource = TokenResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Override update agar token tidak digenerate ulang,
     * cukup update expires_at atau name saja.
     */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // update hanya field non-token
        $record->update([
            'name'       => $data['name'],
            'expires_at' => $data['expires_at'],
            'tokenable_id'   => $data['tokenable_id'],
            'tokenable_type' => $data['tokenable_type'],
        ]);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        // setelah simpan, redirect ke list
        return $this->getResource()::getUrl('index');
    }
}
