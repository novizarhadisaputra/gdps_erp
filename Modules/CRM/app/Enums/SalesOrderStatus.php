<?php

namespace Modules\CRM\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum SalesOrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Submitted => __('Submitted'),
            self::Approved => __('Approved'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'info',
            self::Approved => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => Heroicon::Document,
            self::Submitted => Heroicon::PaperAirplane,
            self::Approved => Heroicon::CheckCircle,
            self::Cancelled => Heroicon::NoSymbol,
        };
    }
}
