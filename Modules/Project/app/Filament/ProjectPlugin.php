<?php

namespace Modules\Project\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class ProjectPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Project';
    }

    public function getId(): string
    {
        return 'project';
    }

    public function boot(Panel $panel): void
    {
        $module = $this->getModule();
        $panel
            ->discoverResources(in: $module->appPath('Filament'.DIRECTORY_SEPARATOR.'Resources'), for: $module->appNamespace('\\Filament\\Resources'))
            ->discoverClusters(in: $module->appPath('Filament'.DIRECTORY_SEPARATOR.'Clusters'), for: $module->appNamespace('\\Filament\\Clusters'))
            ->discoverPages(in: $module->appPath('Filament'.DIRECTORY_SEPARATOR.'Clusters'.DIRECTORY_SEPARATOR.'Project'.DIRECTORY_SEPARATOR.'Pages'), for: $module->appNamespace('\\Filament\\Clusters\\Project\\Pages'))
            ->discoverWidgets(in: $module->appPath('Filament'.DIRECTORY_SEPARATOR.'Clusters'.DIRECTORY_SEPARATOR.'Project'.DIRECTORY_SEPARATOR.'Widgets'), for: $module->appNamespace('\\Filament\\Clusters\\Project\\Widgets'));
    }
}
