<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\RevenueTypeResource;

class ListRevenueTypes extends ListRecords
{
    protected static string $resource = RevenueTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
