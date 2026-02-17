<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\Schemas\ServiceLineForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\ServiceLineResource;

class ListServiceLines extends ListRecords
{
    protected static string $resource = ServiceLineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make()
                ->schema(fn (Schema $schema) => ServiceLineForm::configure($schema)),
        ];
    }
}
