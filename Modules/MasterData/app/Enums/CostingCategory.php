<?php

namespace Modules\MasterData\Enums;

use Filament\Support\Contracts\HasLabel;

enum CostingCategory: string implements HasLabel
{
    case ToolsEquipment = 'tools_equipment';
    case MaterialConsumables = 'material_consumables';
    case ItSystem = 'it_system';
    case Vehicle = 'vehicle';
    case Infrastructure = 'infrastructure';
    case Manpower = 'manpower'; // For Manpower Template later
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ToolsEquipment => 'Tools & Equipment',
            self::MaterialConsumables => 'Material / Consumables',
            self::ItSystem => 'IT System / Software',
            self::Vehicle => 'Vehicle (Rental/Ops)',
            self::Infrastructure => 'Infrastructure Support',
            self::Manpower => 'Manpower / Labor',
            self::Other => 'Other Operational Cost',
        };
    }
}
