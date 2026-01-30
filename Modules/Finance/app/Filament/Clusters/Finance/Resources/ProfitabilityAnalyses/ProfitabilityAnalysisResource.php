<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Finance\Filament\Clusters\Finance\FinanceCluster;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\ListProfitabilityAnalyses;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisInfolist;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Tables\ProfitabilityAnalysesTable;
use Modules\Finance\Models\ProfitabilityAnalysis;

class ProfitabilityAnalysisResource extends Resource
{
    protected static ?string $model = ProfitabilityAnalysis::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = FinanceCluster::class;

    protected static ?int $navigationSort = 1;

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

    public static function getPages(): array
    {
        return [
            'index' => ListProfitabilityAnalyses::route('/'),
            'create' => \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\CreateProfitabilityAnalysis::route('/create'),
            'edit' => \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\EditProfitabilityAnalysis::route('/{record}/edit'),
            'view' => \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\ViewProfitabilityAnalysis::route('/{record}'),
        ];
    }
}
