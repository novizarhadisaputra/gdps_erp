<?php

namespace Modules\Finance\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccrueInvoiceMappingStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Reversed = 'reversed';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Reversed => 'Reversed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'info',
            self::Reversed => 'success',
            self::Cancelled => 'danger',
        };
    }
}
