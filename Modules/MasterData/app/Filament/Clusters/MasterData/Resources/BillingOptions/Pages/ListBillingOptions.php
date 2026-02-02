<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BillingOptions\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BillingOptions\BillingOptionResource;

class ListBillingOptions extends ListRecords
{
    protected static string $resource = BillingOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
