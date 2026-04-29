<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkmConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkmConfigs\BpjsJkmConfigResource;

class CreateBpjsJkmConfig extends CreateRecord
{
    protected static string $resource = BpjsJkmConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Tambahkan konfigurasi baru untuk Jaminan Kematian (JKM).';
    }
}
