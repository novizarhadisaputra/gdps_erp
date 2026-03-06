<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Pages\CreateCostingTemplateItem;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Pages\EditCostingTemplateItem;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Pages\ListCostingTemplateItems;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Schemas\CostingTemplateItemForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Tables\CostingTemplateItemsTable;
use Modules\CRM\Models\CostingTemplateItem;

class CostingTemplateItemResource extends Resource
{
    protected static ?string $model = CostingTemplateItem::class;

    protected static ?string $label = 'Costing Tool & Equipment';

    protected static ?string $pluralLabel = 'Costing Tools & Equipment';

    protected static bool $isNested = true;

    protected static ?string $parentResource = CostingTemplateResource::class;

    public static function form(Schema $schema): Schema
    {
        return CostingTemplateItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostingTemplateItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostingTemplateItems::route('/'),
            'create' => CreateCostingTemplateItem::route('/create'),
            'edit' => EditCostingTemplateItem::route('/{record}/edit'),
        ];
    }
}
