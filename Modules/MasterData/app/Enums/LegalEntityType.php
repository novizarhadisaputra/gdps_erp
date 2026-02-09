<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum LegalEntityType: string implements HasLabel
{
    case PT = 'PT';
    case CV = 'CV';
    case UD = 'UD';
    case Firma = 'Firma';
    case Koperasi = 'Koperasi';
    case Yayasan = 'Yayasan';
    case Individual = 'Individual';
    case Other = 'Other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PT => 'PT (Limited Liability Company)',
            self::CV => 'CV (Limited Partnership)',
            self::UD => 'UD (Trading Business)',
            self::Firma => 'Firma (General Partnership)',
            self::Koperasi => 'Cooperative',
            self::Yayasan => 'Foundation',
            self::Individual => 'Individual',
            self::Other => 'Other',
        };
    }
}
