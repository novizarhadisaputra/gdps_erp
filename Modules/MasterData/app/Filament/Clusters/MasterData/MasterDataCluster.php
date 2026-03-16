<?php

namespace Modules\MasterData\Filament\Clusters\MasterData;

use Filament\Clusters\Cluster;

class MasterDataCluster extends Cluster
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Master Data';

    protected static ?string $slug = 'master-data';

    protected static ?int $navigationSort = 1;
}
