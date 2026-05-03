<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\Pages;

use Illuminate\Database\Eloquent\Model;
use Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\ChartOfAccountResource;
use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Resources\Pages\TreePage;

class ManageChartOfAccounts extends TreePage
{
    protected static string $resource = ChartOfAccountResource::class;

    protected static ?string $title = 'Chart of Accounts';

    public static function tree(Tree $tree): Tree
    {
        return $tree;
    }

    public function getTreeRecordTitle(?Model $record = null): string
    {
        return "{$record->code} - {$record->name}";
    }
}
