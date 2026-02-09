<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum ApprovalSignatureType: string implements HasLabel
{
    case Reviewer = 'Reviewer';
    case Approver = 'Approver';
    case Acknowledger = 'Acknowledger';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Reviewer => 'Reviewer',
            self::Approver => 'Approver',
            self::Acknowledger => 'Acknowledger',
        };
    }
}
