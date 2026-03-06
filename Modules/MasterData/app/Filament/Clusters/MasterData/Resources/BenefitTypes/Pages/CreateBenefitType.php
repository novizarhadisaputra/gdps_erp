<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\BenefitTypeResource;

class CreateBenefitType extends CreateRecord
{
    protected static string $resource = BenefitTypeResource::class;
}
