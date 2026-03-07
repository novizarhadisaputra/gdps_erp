<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\PartnerFeeTypeResource;

class CreatePartnerFeeType extends CreateRecord
{
    protected static string $resource = PartnerFeeTypeResource::class;

    public function getSubheading(): ?string
    {
        return 'Register a new partner fee type with its specific billing rules.';
    }
}
