<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum BpjsType: string implements HasLabel
{
    case Employment = 'employment';
    case Health = 'health';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Employment => 'Employment',
            self::Health => 'Health',
        };
    }
}
