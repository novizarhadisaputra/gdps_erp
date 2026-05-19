<?php

namespace Modules\CRM\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum ProposalStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Converted = 'converted';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Sent => __('Sent'),
            self::Submitted => __('Submitted'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
            self::Converted => __('Converted'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'warning',
            self::Submitted => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Converted => 'primary',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => Heroicon::Document,
            self::Sent => Heroicon::Envelope,
            self::Submitted => Heroicon::PaperAirplane,
            self::Approved => Heroicon::CheckCircle,
            self::Rejected => Heroicon::XCircle,
            self::Converted => Heroicon::DocumentDuplicate,
            self::Cancelled => Heroicon::NoSymbol,
        };
    }
}
