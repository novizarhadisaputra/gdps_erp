<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses;

use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Finance\Filament\Clusters\Finance\FinanceCluster;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\ListProfitabilityAnalyses;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\ManageProfitabilityAnalysisActuals;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\ManageProfitabilityAnalysisRevisions;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\SummaryProfitabilityAnalysis;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Pages\ViewProfitabilityAnalysisRevision;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisInfolist;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Tables\ProfitabilityAnalysesTable;
use Modules\Finance\Models\ProfitabilityAnalysis;

class ProfitabilityAnalysisResource extends Resource
{
    protected static ?string $model = ProfitabilityAnalysis::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $cluster = FinanceCluster::class;

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            SummaryProfitabilityAnalysis::class,
            ManageProfitabilityAnalysisActuals::class,
            ManageProfitabilityAnalysisRevisions::class,
        ]);
    }

    /*
    public static function getPages(): array
    {
        return [
            'index' => ListProfitabilityAnalyses::route('/'),
            'create' => \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\CreateProfitabilityAnalysis::route('/create'),
            'edit' => \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\EditProfitabilityAnalysis::route('/{record}/edit'),
            'view' => \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\ViewProfitabilityAnalysis::route('/{record}'),
        ];
    }
    */
    public static function getPages(): array
    {
        return [
            'index' => ListProfitabilityAnalyses::route('/'),
            'view' => SummaryProfitabilityAnalysis::route('/{record}'),
            'actuals' => ManageProfitabilityAnalysisActuals::route('/{record}/actuals'),
            'revisions' => ManageProfitabilityAnalysisRevisions::route('/{record}/revisions'),
            'view-revision' => ViewProfitabilityAnalysisRevision::route('/{record}/revisions/{relatedRecord}'),
        ];
    }
}
