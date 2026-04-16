<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Resources\ProfitabilityAnalysisWeekly;

use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Resources\ProfitabilityAnalysisWeekly\Schemas\ProfitabilityAnalysisWeeklyForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Resources\ProfitabilityAnalysisWeekly\Tables\ProfitabilityAnalysisWeekliesTable;
use Modules\Finance\Models\ProfitabilityAnalysisWeekly;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProfitabilityAnalysisWeeklyResource extends Resource
{
    protected static ?string $model = ProfitabilityAnalysisWeekly::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static bool $isNested = true;

    protected static ?string $parentResource = \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\ProfitabilityAnalysisMonthlyResource::class;

    protected static bool $isScopedToParentResource = true;

    protected static ?string $slug = 'weekly-updates';

    protected static ?string $label = 'Weekly Update';

    protected static ?string $pluralLabel = 'Weekly Updates';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ProfitabilityAnalysisWeeklyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfitabilityAnalysisWeekliesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            // Note: Pages for this resource are managed via ManageRelatedRecords in Parent
        ];
    }
}
