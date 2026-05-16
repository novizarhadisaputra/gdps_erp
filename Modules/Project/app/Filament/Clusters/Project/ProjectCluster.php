<?php

namespace Modules\Project\Filament\Clusters\Project;

use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class ProjectCluster extends Cluster
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $navigationLabel = 'Project';

    protected static ?int $navigationSort = 4;
}
