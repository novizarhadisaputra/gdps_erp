<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtConfigs\JhtConfigResource;

class CreateJhtConfig extends CreateRecord
{
    protected static string $resource = JhtConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Tambahkan konfigurasi baru untuk Jaminan Hari Tua (JHT).';
    }
}
