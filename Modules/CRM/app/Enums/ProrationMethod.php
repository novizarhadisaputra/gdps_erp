<?php

namespace Modules\CRM\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProrationMethod: string implements HasColor, HasLabel
{
    case Equal = 'equal';
    case Daily = 'daily';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Equal => __('Equal Monthly'),
            self::Daily => __('Daily Prorated'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Equal => 'gray',
            self::Daily => 'info',
        };
    }
}
