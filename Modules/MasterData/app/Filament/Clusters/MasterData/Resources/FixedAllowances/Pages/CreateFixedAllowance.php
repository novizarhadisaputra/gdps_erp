<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\FixedAllowanceResource;

class CreateFixedAllowance extends CreateRecord
{
    protected static string $resource = FixedAllowanceResource::class;

    public function getSubheading(): ?string
    {
        return 'Define a new fixed allowance type with its BPJS and tax configurations.';
    }
}
