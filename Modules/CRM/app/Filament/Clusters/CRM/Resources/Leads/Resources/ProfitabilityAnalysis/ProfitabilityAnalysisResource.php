<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis;

use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages\CreateProfitabilityAnalysis;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages\EditProfitabilityAnalysis;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages\ListProfitabilityAnalyses;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages\ManageProfitabilityAnalysisRevisions;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages\SummaryProfitabilityAnalysis;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\Pages\ViewProfitabilityAnalysisRevision;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisInfolist;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Tables\ProfitabilityAnalysesTable;
use Modules\Finance\Models\ProfitabilityAnalysis;

class ProfitabilityAnalysisResource extends Resource
{
    protected static ?string $model = ProfitabilityAnalysis::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    protected static ?string $navigationLabel = 'Profitability Analysis';

    protected static ?string $pluralLabel = 'Profitability Analysis';

    protected static ?string $singularLabel = 'Profitability Analysis';

    protected static ?string $slug = 'profitability-analysis';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChartBar;

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

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            SummaryProfitabilityAnalysis::class,
            EditProfitabilityAnalysis::class,
            ManageProfitabilityAnalysisRevisions::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfitabilityAnalyses::route('/'),
            'create' => CreateProfitabilityAnalysis::route('/create'),
            'view' => SummaryProfitabilityAnalysis::route('/{record}'),
            'edit' => EditProfitabilityAnalysis::route('/{record}/edit'),
            'revisions' => ManageProfitabilityAnalysisRevisions::route('/{record}/revisions'),
            'view-revision' => ViewProfitabilityAnalysisRevision::route('/{record}/revisions/{relatedRecord}'),
        ];
    }
}
