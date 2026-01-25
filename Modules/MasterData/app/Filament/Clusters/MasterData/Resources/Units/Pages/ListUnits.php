<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\UnitResource;
use Modules\MasterData\Services\UnitService;

class ListUnits extends ListRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No header actions for read-only resource
        ];
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return app(UnitService::class)->getUnits(
            page: $this->getTablePage(),
            perPage: $this->getTableRecordsPerPage(),
            search: $this->getTableSearch(),
        );
    }
}
