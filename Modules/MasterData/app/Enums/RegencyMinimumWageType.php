<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum RegencyMinimumWageType: string implements HasLabel
{
    case Kabupaten = 'Kabupaten';
    case Kota = 'Kota';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
