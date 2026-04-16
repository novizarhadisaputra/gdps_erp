<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Finance\Models\ProfitabilityAnalysisUpdate;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\ProfitabilityAnalysisActualResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate\Pages\CreateProfitabilityAnalysisUpdate;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate\Pages\EditProfitabilityAnalysisUpdate;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate\Pages\ViewProfitabilityAnalysisUpdate;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate\Schemas\ProfitabilityAnalysisUpdateForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate\Tables\ProfitabilityAnalysisUpdatesTable;

class ProfitabilityAnalysisUpdateResource extends Resource
{
    protected static ?string $model = ProfitabilityAnalysisUpdate::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProfitabilityAnalysisActualResource::class;

    protected static ?string $slug = 'weekly-updates';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ProfitabilityAnalysisUpdateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfitabilityAnalysisUpdatesTable::configure($table);
    }

    public static function getRecordSubNavigation($page): array
    {
        return $page->generateNavigationItems([
            ViewProfitabilityAnalysisUpdate::class,
            EditProfitabilityAnalysisUpdate::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateProfitabilityAnalysisUpdate::route('/create'),
            'view' => ViewProfitabilityAnalysisUpdate::route('/{record}'),
            'edit' => EditProfitabilityAnalysisUpdate::route('/{record}/edit'),
        ];
    }
}
