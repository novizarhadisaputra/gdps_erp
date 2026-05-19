<?php

namespace Modules\CRM\Enums;

use Filament\Support\Contracts\HasLabel;

enum SalesOrderType: string implements HasLabel
{
    case Internal = 'internal';
    case External = 'external';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Internal => __('Internal'),
            self::External => __('External'),
        };
    }
}
