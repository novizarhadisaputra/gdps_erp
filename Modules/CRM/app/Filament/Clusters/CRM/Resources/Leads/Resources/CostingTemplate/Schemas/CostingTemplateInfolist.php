<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CostingTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('pic.name')->label('PIC'),
            TextEntry::make('total_monthly_cost')->money('IDR'),
            TextEntry::make('description'),
        ]);
    }
}
