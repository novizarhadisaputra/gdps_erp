<?php

namespace Modules\CRM\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum LeadStatus: string implements HasColor, HasIcon, HasLabel
{
    case Lead = 'lead';
    case Approach = 'approach';
    case Proposal = 'proposal';
    case Negotiation = 'negotiation';
    case Contract = 'contract';
    case Won = 'won';
    case ClosedLost = 'closed_lost';
    case Postponed = 'postponed';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Lead => __('Lead'),
            self::Approach => __('Approach'),
            self::Proposal => __('Proposal'),
            self::Negotiation => __('Negotiation'),
            self::Contract => __('Contract'),
            self::Won => __('Won'),
            self::ClosedLost => __('Closed Lost'),
            self::Postponed => __('Postponed'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Lead => 'gray',
            self::Approach => 'info',
            self::Proposal => 'primary',
            self::Negotiation => 'warning',
            self::Contract => 'success',
            self::Won => 'success',
            self::ClosedLost => 'danger',
            self::Postponed => 'warning',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Lead => Heroicon::Funnel,
            self::Approach => Heroicon::ChatBubbleBottomCenterText,
            self::Proposal => Heroicon::DocumentText,
            self::Negotiation => Heroicon::Scale,
            self::Contract => Heroicon::DocumentCheck,
            self::Won => Heroicon::Trophy,
            self::ClosedLost => Heroicon::XCircle,
            self::Postponed => Heroicon::PauseCircle,
            self::Cancelled => Heroicon::NoSymbol,
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::Lead => 1,
            self::Approach => 2,
            self::Proposal => 3,
            self::Negotiation => 4,
            self::Contract => 5,
            self::Won => 6,
            default => 99,
        };
    }
}
