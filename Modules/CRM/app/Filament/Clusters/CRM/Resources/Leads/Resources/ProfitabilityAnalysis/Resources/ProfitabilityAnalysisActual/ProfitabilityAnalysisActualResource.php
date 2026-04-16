<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Finance\Models\ProfitabilityAnalysisActual;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Schemas\ProfitabilityAnalysisActualForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Tables\ProfitabilityAnalysisActualsTable;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\Pages\CreateProfitabilityAnalysisActual;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\Pages\EditProfitabilityAnalysisActual;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\Pages\ManageWeeklyUpdates;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\Pages\ViewMonthlyActual;

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
