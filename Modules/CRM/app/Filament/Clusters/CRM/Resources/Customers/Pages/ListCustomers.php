<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\CRM\Filament\Clusters\CRM\Resources\Customers\CustomerResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Schemas\CustomerForm;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make()
                ->schema(fn (Schema $schema) => CustomerForm::configure($schema)),
        ];
    }
}
