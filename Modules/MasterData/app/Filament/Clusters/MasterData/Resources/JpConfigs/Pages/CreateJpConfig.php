<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JpConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpConfigs\JpConfigResource;

class CreateJpConfig extends CreateRecord
{
    protected static string $resource = JpConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Tambahkan konfigurasi baru untuk Jaminan Pensiun (JP).';
    }
}
