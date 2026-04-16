<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly;

use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Pages;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;

class ProfitabilityAnalysisMonthlyResource extends Resource
{
    protected static ?string $model = ProfitabilityAnalysisMonthly::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Calendar;

    protected static bool $isScopedToParentResource = true;

    protected static ?string $slug = 'monthly-performance';

    protected static ?string $label = 'Monthly Performance';

    protected static ?string $pluralLabel = 'Monthly Performance';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ViewProfitabilityAnalysisMonthly::route('/'),
            'create' => Pages\CreateProfitabilityAnalysisMonthly::route('/create'),
            'edit' => Pages\EditProfitabilityAnalysisMonthly::route('/{record}/edit'),
            'view' => Pages\ViewProfitabilityAnalysisMonthly::route('/{record}'),
            'weeklies' => Pages\ManageWeeklyUpdates::route('/{record}/weeklies'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getRecordSubNavigation(\Filament\Resources\Pages\Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewProfitabilityAnalysisMonthly::class,
            Pages\EditProfitabilityAnalysisMonthly::class,
            Pages\ManageWeeklyUpdates::class,
        ]);
    }
}
