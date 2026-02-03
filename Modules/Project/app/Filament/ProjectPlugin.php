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
        //
    }
}
