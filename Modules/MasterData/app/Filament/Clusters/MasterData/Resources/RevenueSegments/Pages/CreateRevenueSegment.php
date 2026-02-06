<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\RevenueSegmentResource;

class CreateRevenueSegment extends CreateRecord
{
    protected static string $resource = RevenueSegmentResource::class;
}
