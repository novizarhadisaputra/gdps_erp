<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\PtkpConfigResource;

class CreatePtkpConfig extends CreateRecord
{
    protected static string $resource = PtkpConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Define a new PTKP code with its corresponding annual amount.';
    }
}
