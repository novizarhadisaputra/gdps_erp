<?php

namespace Modules\CRM\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReminderStatus: string implements HasLabel
{
    case SixMonths = '6_month';
    case ThreeMonths = '3_month';
    case OneMonth = '1_month';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SixMonths => __('6 Months Before'),
            self::ThreeMonths => __('3 Months Before'),
            self::OneMonth => __('1 Month Before'),
        };
    }
}
