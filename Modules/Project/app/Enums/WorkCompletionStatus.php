<?php

namespace Modules\Project\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum WorkCompletionStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Sent = 'sent';
    case Signed = 'signed';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::Sent => 'Sent',
            self::Signed => 'Signed',
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
            self::Signed => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-m-pencil',
            self::Submitted => 'heroicon-m-paper-airplane',
            self::Sent => 'heroicon-m-paper-airplane',
            self::Signed => 'heroicon-m-pencil-square',
            self::Approved => 'heroicon-m-check-circle',
            self::Rejected => 'heroicon-m-x-circle',
        };
    }
}
