<?php

namespace Modules\CRM\Filament\Clusters\CRM\Pages;

use App\Filament\Pages\AnalyticsBasePage;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Widgets\CRMStatsOverviewWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\MonthlyPerformanceTrendWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\ProductClusterChartWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\RevenueSegmentDonutWidget;

class CRMAnalyticsPage extends AnalyticsBasePage
{
    public function getSubheading(): ?string
    {
        return __('Visual insights and analytics for CRM data (Updated per April Sales Plan).');
    }

    public static function getNavigationLabel(): string
    {
        return __('Analytics');
    }

    public function getTitle(): string
    {
        return __('CRM Analytics Dashboard');
    }

    protected static ?string $slug = 'crm-analytics';

    protected static ?string $cluster = CRMCluster::class;

    protected static ?int $navigationSort = 0;

    public function getWidgets(): array
    {
        return [
            CRMStatsOverviewWidget::class,
            RevenueSegmentDonutWidget::class,
            ProductClusterChartWidget::class,
            MonthlyPerformanceTrendWidget::class,
        ];
    }

    public function getWidgetColumns(): int|array
    {
        return 2;
    }
}
