<?php

namespace Modules\MasterData\Observers;

use Illuminate\Support\Str;
use Modules\MasterData\Models\DirectCostCategory;

class DirectCostCategoryObserver
{
    public function saving(DirectCostCategory $category): void
    {
        if (empty($category->code)) {
            $category->code = strtoupper(Str::slug($category->name, '_'));
        }
    }
}
