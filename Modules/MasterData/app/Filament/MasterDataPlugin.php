<?php

namespace Modules\MasterData\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class MasterDataPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'MasterData';
    }

    public function getId(): string
    {
        return 'masterdata';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
