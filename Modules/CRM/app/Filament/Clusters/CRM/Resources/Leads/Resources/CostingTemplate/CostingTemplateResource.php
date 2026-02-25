<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages\CreateCostingTemplate;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages\EditCostingTemplate;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages\ListCostingTemplates;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages\ViewCostingTemplate;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas\CostingTemplateForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas\CostingTemplateInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Tables\CostingTemplatesTable;
use Modules\CRM\Models\CostingTemplate;

class CostingTemplateResource extends Resource
{
    protected static ?string $model = CostingTemplate::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    public static function form(Schema $schema): Schema
    {
        return CostingTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostingTemplatesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CostingTemplateInfolist::configure($schema);
    }

    public static function getRecordSubNavigation(\Filament\Resources\Pages\Page $page): array
    {
        return $page->generateNavigationItems([
            ViewCostingTemplate::class,
            EditCostingTemplate::class,
            Pages\ManageCostingTemplateItems::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostingTemplates::route('/'),
            'create' => CreateCostingTemplate::route('/create'),
            'view' => ViewCostingTemplate::route('/{record}'),
            'edit' => EditCostingTemplate::route('/{record}/edit'),
            'items' => Pages\ManageCostingTemplateItems::route('/{record}/items'),
        ];
    }
}
