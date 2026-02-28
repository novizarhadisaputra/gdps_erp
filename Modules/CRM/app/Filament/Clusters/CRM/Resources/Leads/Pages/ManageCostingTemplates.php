<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas\CostingTemplateForm;
use Modules\CRM\Traits\CanImportAi;

class ManageCostingTemplates extends ManageRelatedRecords
{
    use CanImportAi;

    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'costingTemplates';

    protected static ?string $relatedResource = CostingTemplateResource::class;

    protected static ?string $title = 'Costing Templates';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return CostingTemplateResource::form($schema);
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return CostingTemplateResource::table($table)
            ->headerActions([
                $this->getImportCostingAiAction(),
                CreateAction::make()
                    ->schema(fn (\Filament\Schemas\Schema $schema) => CostingTemplateResource::form($schema))
                    ->fillForm(fn () => CostingTemplateForm::getAutoFillData($this->getOwnerRecord())),
            ]);
    }
}
