<?php

namespace Modules\Finance\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FinancePlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Finance';
    }

    public function getId(): string
    {
        return 'finance';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
