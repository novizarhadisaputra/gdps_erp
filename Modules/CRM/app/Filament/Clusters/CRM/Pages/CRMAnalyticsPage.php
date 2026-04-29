<?php

namespace Modules\CRM\Filament\Clusters\CRM\Pages;

use App\Filament\Pages\AnalyticsBasePage;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Widgets\CRMStatsOverviewWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\MonthlyRevenueTrendWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\PerformanceOverviewWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\RevenueForecastWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\SalesPerformanceChartWidget;

class CRMAnalyticsPage extends AnalyticsBasePage
{
    public function getSubheading(): ?string
    {
        return 'Visual insights and analytics for CRM data.';
    }

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?string $title = 'CRM Analytics Dashboard';

    protected static ?string $slug = 'crm-analytics';

    protected static ?string $cluster = CRMCluster::class;

    protected static ?int $navigationSort = 0;

    public function getWidgets(): array
    {
        return [
            // Row 1: Sales Activity Stats
            CRMStatsOverviewWidget::class,

            // Row 2: Financial Performance Stats
            PerformanceOverviewWidget::class,

            // Row 3: Revenue Performance & Trends
            MonthlyRevenueTrendWidget::class,
            SalesPerformanceChartWidget::class,

            // Row 4: Pipeline & Projections
            RevenueForecastWidget::class,
        ];
    }

    public function getWidgetColumns(): int|array
    {
        return 1;
    }
}
