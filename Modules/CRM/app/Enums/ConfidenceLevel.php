<?php

namespace Modules\CRM\Enums;

use Filament\Support\Contracts\HasLabel;

enum ConfidenceLevel: string implements HasLabel
{
    case Optimistic = 'optimistic';
    case Moderate = 'moderate';
    case Pessimistic = 'pessimistic';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Optimistic => 'Optimistic',
            self::Moderate => 'Moderate',
            self::Pessimistic => 'Pessimistic',
        };
    }
}
