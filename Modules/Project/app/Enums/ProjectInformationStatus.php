<?php

namespace Modules\Project\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

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

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Planning => Heroicon::Document,
            self::Ongoing => Heroicon::Play,
            self::Closed => Heroicon::CheckCircle,
        };
    }
}
