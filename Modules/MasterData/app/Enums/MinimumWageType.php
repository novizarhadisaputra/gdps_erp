<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum MinimumWageType: string implements HasLabel
{
    case Regency = 'Regency';
    case City = 'City';
    case Province = 'Province';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
