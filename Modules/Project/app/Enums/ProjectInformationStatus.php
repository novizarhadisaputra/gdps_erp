<?php

namespace Modules\Project\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProjectInformationStatus: string implements HasColor, HasIcon, HasLabel
{
    case Planning = 'planning';
    case Ongoing = 'ongoing';
    case Closed = 'closed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Planning => 'Planning',
            self::Ongoing => 'Ongoing',
            self::Closed => 'Closed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Planning => 'gray',
            self::Ongoing => 'primary',
            self::Closed => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Planning => 'heroicon-m-document',
            self::Ongoing => 'heroicon-m-play',
            self::Closed => 'heroicon-m-check-circle',
        };
    }
}
