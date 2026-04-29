<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsHealthConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsHealthConfigs\BpjsHealthConfigResource;

class CreateBpjsHealthConfig extends CreateRecord
{
    protected static string $resource = BpjsHealthConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Tambahkan konfigurasi baru untuk Jaminan Kesehatan (BPJS Kesehatan).';
    }
}
