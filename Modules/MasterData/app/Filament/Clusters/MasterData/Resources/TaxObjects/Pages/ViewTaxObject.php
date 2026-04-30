<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\TaxObjectResource;

class ViewTaxObject extends ViewRecord
{
    protected static string $resource = TaxObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
