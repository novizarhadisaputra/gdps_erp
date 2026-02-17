<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\WorkSchemeResource;

class ListWorkSchemes extends ListRecords
{
    protected static string $resource = WorkSchemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make()
                ->schema(fn (Schema $schema) => WorkSchemeForm::configure($schema)),
        ];
    }
}
