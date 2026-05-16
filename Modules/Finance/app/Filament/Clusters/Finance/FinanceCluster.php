<?php

namespace Modules\Finance\Filament\Clusters\Finance;

use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class FinanceCluster extends Cluster
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 3;
}
