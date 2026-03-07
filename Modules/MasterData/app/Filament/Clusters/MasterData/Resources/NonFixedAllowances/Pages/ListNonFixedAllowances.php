<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\NonFixedAllowanceResource;

class ListNonFixedAllowances extends ListRecords
{
    protected static string $resource = NonFixedAllowanceResource::class;

    public function getSubheading(): ?string
    {
        return 'Overview of non-fixed allowances and their calculation bases (e.g., per day, per hour).';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
