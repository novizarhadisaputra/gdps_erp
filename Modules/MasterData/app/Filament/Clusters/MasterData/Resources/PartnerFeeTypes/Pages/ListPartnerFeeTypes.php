<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\PartnerFeeTypeResource;

class ListPartnerFeeTypes extends ListRecords
{
    protected static string $resource = PartnerFeeTypeResource::class;

    public function getSubheading(): ?string
    {
        return 'Maintain various fee types for partners, including calculation bases and tax settings.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
