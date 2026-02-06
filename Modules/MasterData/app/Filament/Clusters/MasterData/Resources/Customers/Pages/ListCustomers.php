<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\CustomerResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerForm;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->schema(fn (Schema $schema) => CustomerForm::configure($schema)),
        ];
    }
}
