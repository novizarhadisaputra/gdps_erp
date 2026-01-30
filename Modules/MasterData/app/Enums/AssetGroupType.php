<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssetGroupType: string implements HasLabel
{
    case TangibleNonBuilding = 'tangible_non_building';
    case TangibleBuilding = 'tangible_building';
    case Intangible = 'intangible';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TangibleNonBuilding => 'Harta Berwujud Bukan Bangunan',
            self::TangibleBuilding => 'Harta Berwujud Bangunan',
            self::Intangible => 'Harta Tak Berwujud',
        };
    }
}
