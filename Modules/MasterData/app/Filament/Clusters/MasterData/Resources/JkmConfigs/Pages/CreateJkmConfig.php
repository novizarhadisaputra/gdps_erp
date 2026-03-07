<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmConfigs\JkmConfigResource;

class CreateJkmConfig extends CreateRecord
{
    protected static string $resource = JkmConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Tambahkan konfigurasi baru untuk Jaminan Kematian (JKM).';
    }
}
