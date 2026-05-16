<?php

namespace Modules\Logistics\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class LogisticsPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Logistics';
    }

    public function getId(): string
    {
        return 'logistics';
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
