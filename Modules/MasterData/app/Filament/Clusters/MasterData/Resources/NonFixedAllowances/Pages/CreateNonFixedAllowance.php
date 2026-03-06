<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\NonFixedAllowanceResource;

class CreateNonFixedAllowance extends CreateRecord
{
    protected static string $resource = NonFixedAllowanceResource::class;
}
