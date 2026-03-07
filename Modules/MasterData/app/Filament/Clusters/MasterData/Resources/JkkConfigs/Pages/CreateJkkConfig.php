<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkConfigs\JkkConfigResource;

class CreateJkkConfig extends CreateRecord
{
    protected static string $resource = JkkConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Tambahkan konfigurasi baru untuk Jaminan Kecelakaan Kerja (JKK).';
    }
}
