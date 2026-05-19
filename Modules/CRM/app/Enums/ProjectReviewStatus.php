<?php

namespace Modules\CRM\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum ProjectReviewStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case RevisionNeeded = 'revision_needed';
    case Cancelled = 'cancelled';
    case Review = 'Review';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::InProgress => __('In Progress'),
            self::Completed => __('Completed'),
            self::RevisionNeeded => __('Revision Needed'),
            self::Cancelled => __('Cancelled'),
            self::Review => __('Review'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::InProgress => 'primary',
            self::Completed => 'success',
            self::RevisionNeeded => 'warning',
            self::Cancelled => 'danger',
            self::Review => 'info',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => Heroicon::Pencil,
            self::InProgress => Heroicon::ArrowPath,
            self::Completed => Heroicon::CheckCircle,
            self::RevisionNeeded => Heroicon::ExclamationTriangle,
            self::Cancelled => Heroicon::XCircle,
            self::Review => Heroicon::MagnifyingGlass,
        };
    }
}
