<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\TaxPtkpConfigResource;

class EditTaxPtkpConfig extends EditRecord
{
    protected static string $resource = TaxPtkpConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Adjust PTKP configuration values and categories.';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
