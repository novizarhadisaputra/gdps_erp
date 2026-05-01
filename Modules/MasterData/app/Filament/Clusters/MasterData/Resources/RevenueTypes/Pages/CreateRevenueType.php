<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\RevenueTypeResource;

class CreateRevenueType extends CreateRecord
{
    protected static string $resource = RevenueTypeResource::class;
}
