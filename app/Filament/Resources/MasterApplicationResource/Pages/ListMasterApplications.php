<?php

namespace App\Filament\Resources\MasterApplicationResource\Pages;

use App\Filament\Resources\MasterApplicationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterApplications extends ListRecords
{
    protected static string $resource = MasterApplicationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
