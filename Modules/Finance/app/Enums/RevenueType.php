<?php

namespace Modules\Finance\Enums;

enum RevenueType: string
{
    case MainWork = 'main_work';
    case Overtime = 'overtime';
    case Travel = 'travel';
    case Material = 'material';
    case Buffer = 'buffer';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::MainWork => 'Pekerjaan Utama',
            self::Overtime => 'Lemburan',
            self::Travel => 'SPPD / Perjalanan Dinas',
            self::Material => 'Material',
            self::Buffer => 'Buffer (Tenaga Pengganti)',
            self::Other => 'Lain-lain',
        };
    }
}
