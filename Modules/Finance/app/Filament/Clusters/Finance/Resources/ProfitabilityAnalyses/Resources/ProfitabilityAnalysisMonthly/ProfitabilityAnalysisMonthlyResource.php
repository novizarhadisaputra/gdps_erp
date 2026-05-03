<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly;

use App\Filament\RelationManagers\MeetingRelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Schemas\ProfitabilityAnalysisMonthlyForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Tables\ProfitabilityAnalysisMonthliesTable;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;

class ProfitabilityAnalysisMonthlyResource extends Resource
{
    protected static ?string $model = ProfitabilityAnalysisMonthly::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Calendar;

    protected static bool $isNested = true;

    protected static ?string $parentResource = \Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource::class;

    protected static bool $isScopedToParentResource = true;

    protected static ?string $slug = 'monthly-performance';

    protected static ?string $label = 'Monthly Performance';

    protected static ?string $pluralLabel = 'Monthly Performance';

    public static function form(Schema $schema): Schema
    {
        return ProfitabilityAnalysisMonthlyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfitabilityAnalysisMonthliesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ViewProfitabilityAnalysisMonthly::route('/'),
            'create' => Pages\CreateProfitabilityAnalysisMonthly::route('/create'),
            'edit' => Pages\EditProfitabilityAnalysisMonthly::route('/{record}/edit'),
            'view' => Pages\ViewProfitabilityAnalysisMonthly::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            MeetingRelationManager::class,
        ];
    }

    public static function getRecordSubNavigation(\Filament\Resources\Pages\Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewProfitabilityAnalysisMonthly::class,
            Pages\EditProfitabilityAnalysisMonthly::class,
        ]);
    }
}
