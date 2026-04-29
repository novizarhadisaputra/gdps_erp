<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs\BpjsJkkConfigResource;

class CreateBpjsJkkConfig extends CreateRecord
{
    protected static string $resource = BpjsJkkConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Tambahkan konfigurasi baru untuk Jaminan Kecelakaan Kerja (JKK).';
    }
}
