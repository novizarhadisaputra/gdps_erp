<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum CalculationCapType: string implements HasLabel
{
    case None = 'none';
    case Nominal = 'nominal';
    case Percentage = 'percentage';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::None => 'None',
            self::Nominal => 'Nominal',
            self::Percentage => 'Percentage',
        };
    }
}
