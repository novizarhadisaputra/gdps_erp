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
            self::Optimistic => __('Optimistic'),
            self::Moderate => __('Moderate'),
            self::Pessimistic => __('Pessimistic'),
        };
    }
}
