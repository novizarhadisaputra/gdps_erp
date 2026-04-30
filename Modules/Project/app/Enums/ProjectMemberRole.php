<?php

namespace Modules\Project\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProjectMemberRole: string implements HasColor, HasLabel
{
    case AMS = 'AMS';
    case Oprep = 'Oprep';
    case ProjectManager = 'Project Manager';
    case TechnicalLead = 'Technical Lead';
    case Developer = 'Developer';
    case Member = 'Member';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AMS => 'AMS',
            self::Oprep => 'Oprep',
            self::ProjectManager => 'Project Manager',
            self::TechnicalLead => 'Technical Lead',
            self::Developer => 'Developer',
            self::Member => 'Member',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::AMS => 'info',
            self::Oprep => 'warning',
            self::ProjectManager => 'success',
            self::TechnicalLead => 'danger',
            self::Developer => 'primary',
            self::Member => 'gray',
        };
    }
}
