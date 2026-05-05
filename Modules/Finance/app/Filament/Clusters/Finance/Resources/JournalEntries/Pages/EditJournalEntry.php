<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\JournalEntryResource;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
