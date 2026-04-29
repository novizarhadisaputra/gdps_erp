<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\TaxObjectResource;

class EditTaxObject extends EditRecord
{
    protected static string $resource = TaxObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
