<?php

namespace Modules\CRM\Filament\Clusters\CRM;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class CRMCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'CRM';

    protected static ?string $title = 'CRM';

    protected static ?int $navigationSort = 1;
}
