<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\FixedAllowanceResource;

class ListFixedAllowances extends ListRecords
{
    protected static string $resource = FixedAllowanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
