<?php

namespace Modules\Project\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum WorkCompletionStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Sent = 'sent';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::Sent => 'Sent',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'warning',
            self::Sent => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => Heroicon::Pencil,
            self::Submitted => Heroicon::PaperAirplane,
            self::Sent => Heroicon::PaperAirplane,
            self::Approved => Heroicon::CheckCircle,
            self::Rejected => Heroicon::XCircle,
        };
    }
}
