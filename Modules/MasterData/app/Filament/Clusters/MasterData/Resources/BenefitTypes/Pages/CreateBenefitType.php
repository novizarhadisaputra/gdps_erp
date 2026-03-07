<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\BenefitTypeResource;

class CreateBenefitType extends CreateRecord
{
    protected static string $resource = BenefitTypeResource::class;

    public function getSubheading(): ?string
    {
        return 'Configure a new benefit type and its accrual method.';
    }
}
