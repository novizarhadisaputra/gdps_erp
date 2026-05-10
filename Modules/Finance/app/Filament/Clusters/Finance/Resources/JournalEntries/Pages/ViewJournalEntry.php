<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\JournalEntryResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Traits\HasJournalActions;

class ViewJournalEntry extends ViewRecord
{
    use HasJournalActions;

    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getPrintVoucherAction(),
        ];
    }
}
