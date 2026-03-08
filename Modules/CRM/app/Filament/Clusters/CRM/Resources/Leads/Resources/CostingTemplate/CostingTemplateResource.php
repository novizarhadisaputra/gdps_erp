<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate;

use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages\EditCostingTemplate;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages\ManageCostingTemplateItems;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages\ViewCostingTemplate;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Pages\CreateCostingTemplateItem;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Pages\EditCostingTemplateItem;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Pages\ViewCostingTemplateItem;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas\CostingTemplateForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas\CostingTemplateInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Tables\CostingTemplatesTable;
use Modules\CRM\Models\CostingTemplate;

class CostingTemplateResource extends Resource
{
    protected static ?string $model = CostingTemplate::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    protected static ?string $navigationLabel = 'Tools & Equipment Costing';

    protected static ?string $pluralLabel = 'Tools & Equipment Costing';

    protected static ?string $singularLabel = 'Tools & Equipment Costing';

    protected static ?string $slug = 'tools-equipment-costing';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewCostingTemplate::class,
            EditCostingTemplate::class,
            ManageCostingTemplateItems::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCostingTemplates::route('/'),
            'create' => Pages\CreateCostingTemplate::route('/create'),
            'view' => Pages\ViewCostingTemplate::route('/{record}'),
            'edit' => Pages\EditCostingTemplate::route('/{record}/edit'),
            'items' => ManageCostingTemplateItems::route('/{record}/items'),
            'create-item' => CreateCostingTemplateItem::route('/{record}/items/create'),
            'edit-item' => EditCostingTemplateItem::route('/{record}/items/{relatedRecord}/edit'),
            'view-item' => ViewCostingTemplateItem::route('/{record}/items/{relatedRecord}'),
        ];
    }
}
