<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\JournalEntryResource;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;
}
