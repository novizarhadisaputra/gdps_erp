<?php

namespace Modules\CRM\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LeadStatus: string implements HasColor, HasIcon, HasLabel
{
    case Lead = 'lead';
    case Approach = 'approach';
    case Proposal = 'proposal';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case ClosedLost = 'closed_lost';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Lead => 'Lead',
            self::Approach => 'Approach',
            self::Proposal => 'Proposal',
            self::Negotiation => 'Negotiation',
            self::Won => 'Won',
            self::ClosedLost => 'Closed Lost',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Lead => 'gray',
            self::Approach => 'info',
            self::Proposal => 'primary',
            self::Negotiation => 'warning',
            self::Won => 'success',
            self::ClosedLost => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Lead => 'heroicon-m-funnel',
            self::Approach => 'heroicon-m-chat-bubble-bottom-center-text',
            self::Proposal => 'heroicon-m-document-text',
            self::Negotiation => 'heroicon-m-scale',
            self::Won => 'heroicon-m-trophy',
            self::ClosedLost => 'heroicon-m-x-circle',
        };
    }
}
