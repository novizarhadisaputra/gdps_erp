<?php

namespace Modules\Project\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectChangeRequestType: string implements HasLabel
{
    case Manpower = 'manpower';
    case ScopeOfWork = 'scope_of_work';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Manpower => 'Manpower',
            self::ScopeOfWork => 'Scope of Work',
        };
    }
}
