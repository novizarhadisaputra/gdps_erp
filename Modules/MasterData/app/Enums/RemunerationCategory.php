<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum RemunerationCategory: string implements HasLabel
{
    case Allowance = 'allowance';
    case Benefit = 'benefit';
    case Tax = 'tax';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Allowance => 'Allowance',
            self::Benefit => 'Benefit',
            self::Tax => 'Tax',
            self::Other => 'Other',
        };
    }
}
