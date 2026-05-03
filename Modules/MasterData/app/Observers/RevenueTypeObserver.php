<?php

namespace Modules\MasterData\Observers;

use Modules\MasterData\Models\RevenueType;

class RevenueTypeObserver
{
    public function saving(RevenueType $revenueType): void
    {
        if (empty($revenueType->code)) {
            $revenueType->code = str($revenueType->name)->slug()->replace('-', '_')->toString();
        }
    }
}
