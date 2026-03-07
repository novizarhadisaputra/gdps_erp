<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\NonFixedAllowanceResource;

class CreateNonFixedAllowance extends CreateRecord
{
    protected static string $resource = NonFixedAllowanceResource::class;

    public function getSubheading(): ?string
    {
        return 'Add a new non-fixed allowance with specific calculation settings.';
    }
}
