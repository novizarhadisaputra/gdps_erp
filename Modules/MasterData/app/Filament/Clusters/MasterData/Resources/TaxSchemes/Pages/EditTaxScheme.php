<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\TaxSchemeResource;

class EditTaxScheme extends EditRecord
{
    protected static string $resource = TaxSchemeResource::class;

    public function getSubheading(): ?string
    {
        return 'Update tax scheme configuration and active status.';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
