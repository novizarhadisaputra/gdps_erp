<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\JournalEntryResource;

class ViewJournalEntry extends ViewRecord
{
    protected static string $resource = JournalEntryResource::class;
}
