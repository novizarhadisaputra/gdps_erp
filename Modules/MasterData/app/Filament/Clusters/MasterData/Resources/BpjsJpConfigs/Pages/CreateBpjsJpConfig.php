<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJpConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJpConfigs\BpjsJpConfigResource;

class CreateBpjsJpConfig extends CreateRecord
{
    protected static string $resource = BpjsJpConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Tambahkan konfigurasi baru untuk Jaminan Pensiun (JP).';
    }
}
