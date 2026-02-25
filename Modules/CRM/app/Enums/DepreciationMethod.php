<?php

namespace Modules\CRM\Enums;

use Filament\Support\Contracts\HasLabel;

enum DepreciationMethod: string implements HasLabel
{
    case StraightLine = 'straight_line';
    case DecliningBalance = 'declining_balance';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::StraightLine => 'Straight Line (Linear)',
            self::DecliningBalance => 'Declining Balance (Accelerated)',
        };
    }
}
