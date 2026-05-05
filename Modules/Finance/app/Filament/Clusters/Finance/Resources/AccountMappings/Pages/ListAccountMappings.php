<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Modules\CRM\Models\Customer;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\AccountMappingResource;
use Modules\Finance\Models\AccountMapping;
use Modules\MasterData\Models\ProjectArea;

class ListAccountMappings extends ListRecords
{
    protected static string $resource = AccountMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New account mapping'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(AccountMapping::count()),
            'project_area' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('mappable_type', ProjectArea::class))
                ->badge(AccountMapping::where('mappable_type', ProjectArea::class)->count()),
            'customer' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('mappable_type', Customer::class))
                ->badge(AccountMapping::where('mappable_type', Customer::class)->count()),
        ];
    }
}
