<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJhtConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJhtConfigs\BpjsJhtConfigResource;

class CreateBpjsJhtConfig extends CreateRecord
{
    protected static string $resource = BpjsJhtConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Tambahkan konfigurasi baru untuk Jaminan Hari Tua (JHT).';
    }
}
