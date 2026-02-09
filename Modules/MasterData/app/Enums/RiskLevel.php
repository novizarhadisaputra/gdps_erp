<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum RiskLevel: string implements HasLabel
{
    case VeryLow = 'very_low';
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case VeryHigh = 'very_high';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::VeryLow => 'Very Low',
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::VeryHigh => 'Very High',
        };
    }
}
