<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\BenefitTypeResource;

class ListBenefitTypes extends ListRecords
{
    protected static string $resource = BenefitTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
