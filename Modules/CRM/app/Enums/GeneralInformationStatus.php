<?php

namespace Modules\CRM\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum GeneralInformationStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Submitted => __('Submitted'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => Heroicon::Document,
            self::Submitted => Heroicon::PaperAirplane,
            self::Approved => Heroicon::CheckCircle,
            self::Rejected => Heroicon::XCircle,
        };
    }
}
