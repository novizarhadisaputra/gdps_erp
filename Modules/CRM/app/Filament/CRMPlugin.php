<?php

namespace Modules\CRM\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class CRMPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'CRM';
    }

    public function getId(): string
    {
        return 'crm';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
