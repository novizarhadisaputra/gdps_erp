<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\UnitResource;

class ListUnits extends ListRecords
{
    protected static string $resource = UnitResource::class;

    public function getSubheading(): ?string
    {
        return 'Organizational structure and business units management.';
    }

    protected function getHeaderActions(): array
    {
        return [
            // No header actions for read-only resource
        ];
    }
}
