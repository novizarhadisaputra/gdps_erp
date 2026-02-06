<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\ContractResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Schemas\ContractForm;

class ListContracts extends ListRecords
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->schema(fn (Schema $schema) => ContractForm::configure($schema)),
        ];
    }
}
