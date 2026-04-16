<?php

namespace Modules\Finance\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProfitabilityAnalysisMonthlyStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Finalized = 'finalized';
    case Closed = 'closed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Finalized => 'Finalized',
            self::Closed => 'Closed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Finalized => 'success',
            self::Closed => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil',
            self::Finalized => 'heroicon-o-check-circle',
            self::Closed => 'heroicon-o-lock-closed',
        };
    }
}
