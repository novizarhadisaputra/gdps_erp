<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\ThrBasisTypeResource;

class CreateThrBasisType extends CreateRecord
{
    protected static string $resource = ThrBasisTypeResource::class;

    public function getSubheading(): ?string
    {
        return 'Set up a new THR calculation basis with specific formulas.';
    }
}
