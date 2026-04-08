<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasColor, HasLabel
{
    case Male = 'male';
    case Female = 'female';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Male => 'Mr. (Bapak)',
            self::Female => 'Ms. (Ibu)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Male => 'info',
            self::Female => 'danger',
        };
    }

    public function getSalutation(): string
    {
        return match ($this) {
            self::Male => 'Bapak',
            self::Female => 'Ibu',
        };
    }
}
