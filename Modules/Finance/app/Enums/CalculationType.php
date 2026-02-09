<?php

namespace Modules\Finance\Enums;

use Filament\Support\Contracts\HasLabel;

enum CalculationType: string implements HasLabel
{
    case Nominal = 'nominal';
    case Percentage = 'percentage';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Nominal => 'Nominal (Rp)',
            self::Percentage => 'Percentage (%)',
        };
    }
}
