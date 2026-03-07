<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\TaxSchemeResource;

class ListTaxSchemes extends ListRecords
{
    protected static string $resource = TaxSchemeResource::class;

    public function getSubheading(): ?string
    {
        return 'Maintain different income tax (PPh 21) schemes and their specific rules.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
