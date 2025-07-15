<?php

namespace App\Filament\Resources\MasterApplicationResource\Pages;

use App\Filament\Resources\MasterApplicationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterApplication extends EditRecord
{
    protected static string $resource = MasterApplicationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
