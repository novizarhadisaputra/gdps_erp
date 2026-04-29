<?php

namespace Modules\Finance\Filament\Clusters\Finance\Pages;

use App\Filament\Pages\AnalyticsBasePage;
use Modules\Finance\Filament\Clusters\Finance\FinanceCluster;
use Modules\Finance\Filament\Widgets\BappStatusStats;
use Modules\Finance\Filament\Widgets\FinanceRevenueChart;
use Modules\Finance\Filament\Widgets\ManpowerCompositionDonut;

class FinanceAnalyticsPage extends AnalyticsBasePage
{
    protected static ?string $title = 'Finance Analytics Dashboard';

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?string $slug = 'finance-analytics';

    protected static ?string $cluster = FinanceCluster::class;

    protected static ?int $navigationSort = 0;

    public function getSubheading(): ?string
    {
        return 'Financial performance insights and revenue trends.';
    }

    public function getWidgets(): array
    {
        return [
            BappStatusStats::class,
            FinanceRevenueChart::class,
            ManpowerCompositionDonut::class,
        ];
    }

    public function getWidgetColumns(): int|array
    {
        return 1;
    }
}
