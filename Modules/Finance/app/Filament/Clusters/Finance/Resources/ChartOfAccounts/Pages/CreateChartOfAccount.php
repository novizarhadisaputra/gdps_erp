<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\ChartOfAccountResource;
use Modules\Finance\Models\ChartOfAccount;

class CreateChartOfAccount extends CreateRecord
{
    protected static string $resource = ChartOfAccountResource::class;

    public function mount(): void
    {
        parent::mount();

        if ($parentId = request()->query('parent_id')) {
            $parent = ChartOfAccount::find($parentId);

            $this->form->fill([
                'parent_id' => $parentId,
                'account_type' => $parent?->account_type,
                'code' => $parent?->code.'.',
            ]);
        }
    }
}
