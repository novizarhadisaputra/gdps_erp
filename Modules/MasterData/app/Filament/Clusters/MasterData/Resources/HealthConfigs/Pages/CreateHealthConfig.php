<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\HealthConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\HealthConfigs\HealthConfigResource;

class CreateHealthConfig extends CreateRecord
{
    protected static string $resource = HealthConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Tambahkan konfigurasi baru untuk Jaminan Kesehatan (BPJS Kesehatan).';
    }
}
