<?php

namespace Modules\CRM\Filament\Clusters\CRM;

use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class CRMCluster extends Cluster
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'CRM';

    protected static ?string $title = 'CRM';

    protected static ?string $slug = 'crm';

    protected static ?int $navigationSort = 2;
}
