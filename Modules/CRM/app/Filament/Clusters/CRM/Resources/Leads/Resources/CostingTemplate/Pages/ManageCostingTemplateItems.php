<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\CostingTemplateItemResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Schemas\CostingTemplateItemForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Tables\CostingTemplateItemsTable;

class ManageCostingTemplateItems extends ManageRelatedRecords
{
    protected static string $resource = CostingTemplateResource::class;

    protected static string $relationship = 'costingTemplateItems';

    protected static ?string $relatedResource = CostingTemplateItemResource::class;

    protected static ?string $title = 'Cost Items';

    protected static ?string $navigationLabel = 'Cost Items';

    public function form(Schema $schema): Schema
    {
        return CostingTemplateItemForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return CostingTemplateItemsTable::configure($table)
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
