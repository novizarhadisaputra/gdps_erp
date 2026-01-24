<?php

namespace Modules\MasterData\Filament\Clusters\MasterData;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class MasterDataCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Master Data';

    protected static ?int $navigationSort = 3;
}
