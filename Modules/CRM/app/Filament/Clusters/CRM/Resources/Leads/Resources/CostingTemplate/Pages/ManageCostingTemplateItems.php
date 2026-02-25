<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\CostingTemplateItemResource;

class ManageCostingTemplateItems extends ManageRelatedRecords
{
    protected static string $resource = CostingTemplateResource::class;

    protected static string $relationship = 'costingTemplateItems';

    protected static ?string $relatedResource = CostingTemplateItemResource::class;

    protected static ?string $title = 'Costing Items';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return CostingTemplateItemResource::form($schema);
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return CostingTemplateItemResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->schema(fn (\Filament\Schemas\Schema $schema) => CostingTemplateItemResource::form($schema)),
            ]);
    }
}
