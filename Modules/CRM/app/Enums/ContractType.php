<?php

namespace Modules\CRM\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContractType: string implements HasColor, HasLabel
{
    case Agreement = 'agreement';
    case WorkOrder = 'work_order';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Agreement => 'Agreement',
            self::WorkOrder => 'Work Order',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Agreement => 'primary',
            self::WorkOrder => 'info',
        };
    }
}
