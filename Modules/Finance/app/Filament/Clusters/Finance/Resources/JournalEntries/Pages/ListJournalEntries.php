<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\JournalEntryResource;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
