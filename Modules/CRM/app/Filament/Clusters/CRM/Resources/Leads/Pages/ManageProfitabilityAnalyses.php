<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;

class ManageProfitabilityAnalyses extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'profitabilityAnalyses';

    protected static ?string $relatedResource = ProfitabilityAnalysisResource::class;

    protected static ?string $title = 'Profitability Analyses';

    public function form(Schema $schema): Schema
    {
        return ProfitabilityAnalysisResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ProfitabilityAnalysisResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->schema(fn (Schema $schema) => ProfitabilityAnalysisResource::form($schema)),
            ]);
    }
}
