<?php

namespace Modules\CRM\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProjectReviewStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case RevisionNeeded = 'revision_needed';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::RevisionNeeded => 'Revision Needed',
            self::Cancelled => 'Cancelled',
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
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-m-pencil',
            self::InProgress => 'heroicon-m-arrow-path',
            self::Completed => 'heroicon-m-check-circle',
            self::RevisionNeeded => 'heroicon-m-exclamation-triangle',
            self::Cancelled => 'heroicon-m-x-circle',
        };
    }
}
