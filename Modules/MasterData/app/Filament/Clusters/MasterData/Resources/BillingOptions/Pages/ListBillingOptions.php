<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BillingOptions\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BillingOptions\BillingOptionResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BillingOptions\Schemas\BillingOptionForm;

class ListBillingOptions extends ListRecords
{
    protected static string $resource = BillingOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make()
                ->schema(fn (Schema $schema) => BillingOptionForm::configure($schema)),
        ];
    }
}
