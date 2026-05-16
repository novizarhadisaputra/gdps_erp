<?php

namespace Modules\MasterData\Filament\Clusters\MasterData;

use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class MasterDataCluster extends Cluster
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?string $navigationLabel = 'Master Data';

    protected static ?string $slug = 'master-data';

    protected static ?int $navigationSort = 1;
}
