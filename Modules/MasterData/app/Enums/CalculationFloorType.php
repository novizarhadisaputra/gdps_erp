<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum CalculationFloorType: string implements HasLabel
{
    case None = 'none';
    case Nominal = 'nominal';
    case Umk = 'umk';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::None => 'None',
            self::Nominal => 'Nominal',
            self::Umk => 'UMK/UMP',
        };
    }
}
