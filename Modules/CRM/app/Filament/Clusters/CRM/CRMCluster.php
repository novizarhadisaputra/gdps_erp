<?php

namespace Modules\CRM\Filament\Clusters\CRM;

use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class CRMCluster extends Cluster
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $slug = 'crm';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('CRM');
    }

    public function getTitle(): string
    {
        return __('CRM');
    }
}
