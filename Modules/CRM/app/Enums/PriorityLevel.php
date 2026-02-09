<?php

namespace Modules\CRM\Enums;

use Filament\Support\Contracts\HasLabel;

enum PriorityLevel: string implements HasLabel
{
    case Priority1 = '1';
    case Priority2 = '2';
    case Priority3 = '3';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Priority1 => 'Priority 1',
            self::Priority2 => 'Priority 2',
            self::Priority3 => 'Priority 3',
        };
    }
}
