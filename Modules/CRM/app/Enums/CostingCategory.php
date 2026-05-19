<?php

namespace Modules\CRM\Enums;

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
            self::ToolsEquipment => __('Tools & Equipment'),
            self::MaterialConsumables => __('Material / Consumables'),
            self::ItSystem => __('IT System / Software'),
            self::Vehicle => __('Vehicle (Rental/Ops)'),
            self::Infrastructure => __('Infrastructure Support'),
            self::Manpower => __('Manpower / Labor'),
            self::Other => __('Other Operational Cost'),
        };
    }
}
