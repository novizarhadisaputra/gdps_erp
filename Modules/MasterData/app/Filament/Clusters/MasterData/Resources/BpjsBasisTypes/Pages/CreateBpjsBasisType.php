<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\BpjsBasisTypeResource;

class CreateBpjsBasisType extends CreateRecord
{
    protected static string $resource = BpjsBasisTypeResource::class;

    public function getSubheading(): ?string
    {
        return 'Create a new formula-based BPJS calculation basis.';
    }
}
