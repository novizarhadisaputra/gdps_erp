<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Finance\Models\ProfitabilityAnalysisActual;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Pages\CreateProfitabilityAnalysisActual;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Pages\EditProfitabilityAnalysisActual;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Pages\ManageWeeklyUpdates;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Pages\ViewMonthlyActual;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Schemas\ProfitabilityAnalysisActualForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Tables\ProfitabilityAnalysisActualsTable;

class ProfitabilityAnalysisActualResource extends Resource
{
    protected static ?string $model = ProfitabilityAnalysisActual::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProfitabilityAnalysisResource::class;

    protected static ?string $slug = 'monthly-actuals';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ProfitabilityAnalysisActualForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfitabilityAnalysisActualsTable::configure($table);
    }

    public static function getRecordSubNavigation($page): array
    {
        return $page->generateNavigationItems([
            ViewMonthlyActual::class,
            EditProfitabilityAnalysisActual::class,
            ManageWeeklyUpdates::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateProfitabilityAnalysisActual::route('/create'),
            'view' => ViewMonthlyActual::route('/{record}'),
            'edit' => EditProfitabilityAnalysisActual::route('/{record}/edit'),
            'weekly-updates' => ManageWeeklyUpdates::route('/{record}/weekly-updates'),
        ];
    }
}
