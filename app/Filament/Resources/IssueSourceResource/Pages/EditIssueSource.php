<?php

namespace App\Filament\Resources\IssueSourceResource\Pages;

use App\Filament\Resources\IssueSourceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIssueSource extends EditRecord
{
    protected static string $resource = IssueSourceResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
