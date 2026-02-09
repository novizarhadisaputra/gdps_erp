<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum BpjsCategory: string implements HasLabel
{
    case Health = 'Health';
    case JKK = 'JKK';
    case JKM = 'JKM';
    case JHT = 'JHT';
    case JP = 'JP';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Health => 'BPJS Kesehatan',
            self::JKK => 'BPJS JKK (Kecelakaan Kerja)',
            self::JKM => 'BPJS JKM (Kematian)',
            self::JHT => 'BPJS JHT (Hari Tua)',
            self::JP => 'BPJS JP (Pensiun)',
        };
    }
}
