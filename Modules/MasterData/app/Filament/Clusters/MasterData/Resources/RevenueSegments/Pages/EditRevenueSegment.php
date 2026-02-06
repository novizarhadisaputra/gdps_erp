<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\RevenueSegmentResource;

class EditRevenueSegment extends EditRecord
{
    protected static string $resource = RevenueSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
