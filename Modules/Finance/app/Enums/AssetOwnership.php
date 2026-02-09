<?php

namespace Modules\Finance\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssetOwnership: string implements HasLabel
{
    case GdpsOwned = 'gdps-owned';
    case CustomerOwned = 'customer-owned';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::GdpsOwned => 'GDPS-Owned',
            self::CustomerOwned => 'Customer-Owned',
        };
    }
}
