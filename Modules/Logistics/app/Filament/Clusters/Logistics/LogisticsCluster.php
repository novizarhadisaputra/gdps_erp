<?php

namespace Modules\Logistics\Filament\Clusters\Logistics;

use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class LogisticsCluster extends Cluster
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'Logistics';

    protected static ?string $title = 'Logistics';

    protected static ?string $slug = 'logistics';

    protected static ?int $navigationSort = 3;
}
