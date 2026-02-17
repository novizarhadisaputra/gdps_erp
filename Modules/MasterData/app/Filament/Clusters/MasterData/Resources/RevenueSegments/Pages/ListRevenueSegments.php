<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\RevenueSegmentResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Schemas\RevenueSegmentForm;

class ListRevenueSegments extends ListRecords
{
    protected static string $resource = RevenueSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make()
                ->schema(fn (Schema $schema) => RevenueSegmentForm::configure($schema)),
        ];
    }
}
