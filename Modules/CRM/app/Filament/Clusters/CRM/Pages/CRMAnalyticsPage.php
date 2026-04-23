<?php

namespace Modules\CRM\Filament\Clusters\CRM\Pages;

use App\Filament\Pages\AnalyticsBasePage;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Widgets\CRMStatsOverviewWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\DealStatusDistributionWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\LeadConversionTrendWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\LeadPipelineWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\MonthlyRevenueTrendWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\PerformanceOverviewWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\RevenueForecastWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\SalesPerformanceChartWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\SalesTeamPerformanceWidget;

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
            // Row 1: High-Level Stats (Leads & Financial Performance)
            CRMStatsOverviewWidget::class,
            PerformanceOverviewWidget::class,

            // Row 2: Revenue Performance & Trends (Target vs Actual)
            MonthlyRevenueTrendWidget::class,       // Bar Chart (Monthly)
            SalesPerformanceChartWidget::class,     // Line Chart (Cumulative)

            // Row 3: Pipeline & Projections
            LeadPipelineWidget::class,              // Left: Sales funnel
            RevenueForecastWidget::class,           // Right: Revenue projection

            // Row 4: Conversion & Team Performance
            LeadConversionTrendWidget::class,       // Left: Historical trend
            SalesTeamPerformanceWidget::class,      // Right: Team comparison

            // Row 5: Distribution
            DealStatusDistributionWidget::class,
        ];
    }

    public function getWidgetColumns(): int|array
    {
        return [
            'default' => 1,  // Mobile: single column
            'sm' => 1,       // Small tablet: single column
            'md' => 2,       // Tablet: 2 columns
            'lg' => 2,       // Desktop: 2 columns
            'xl' => 2,       // Large desktop: 2 columns
            '2xl' => 2,      // Extra large: 2 columns
        ];
    }
}
