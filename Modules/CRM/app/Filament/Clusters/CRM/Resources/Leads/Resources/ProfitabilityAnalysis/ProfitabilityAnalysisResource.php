<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages\CreateProfitabilityAnalysis;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages\EditProfitabilityAnalysis;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages\ListProfitabilityAnalyses;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages\ViewProfitabilityAnalysis;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisInfolist;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Tables\ProfitabilityAnalysesTable;
use Modules\Finance\Models\ProfitabilityAnalysis;

class ProfitabilityAnalysisResource extends Resource
{
    protected static ?string $model = ProfitabilityAnalysis::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    public static function form(Schema $schema): Schema
    {
        return ProfitabilityAnalysisForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfitabilityAnalysesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProfitabilityAnalysisInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfitabilityAnalyses::route('/'),
            'create' => CreateProfitabilityAnalysis::route('/create'),
            'view' => ViewProfitabilityAnalysis::route('/{record}'),
            'edit' => EditProfitabilityAnalysis::route('/{record}/edit'),
        ];
    }
}
