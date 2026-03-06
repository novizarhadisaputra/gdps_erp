<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\TaxSchemeResource;

class EditTaxScheme extends EditRecord
{
    protected static string $resource = TaxSchemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
