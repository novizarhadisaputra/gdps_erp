<?php

namespace Modules\Finance\Enums;

enum AccrueRevenueStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';
    case Reversed = 'reversed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Open => 'Open',
            self::Closed => 'Closed',
            self::Reversed => 'Reversed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Open => 'info',
            self::Closed => 'success',
            self::Reversed => 'danger',
        };
    }
}
