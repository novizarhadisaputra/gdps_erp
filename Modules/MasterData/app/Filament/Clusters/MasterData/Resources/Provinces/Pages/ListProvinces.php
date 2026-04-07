<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\ProvinceResource;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Services\WilayahSyncService;

class ListProvinces extends ListRecords
{
    protected static string $resource = ProvinceResource::class;

    public function mount(): void
    {
        parent::mount();

        if (Province::count() === 0) {
            app(WilayahSyncService::class)->syncProvinces();
        }
    }
}
