<?php

namespace Modules\CRM\Filament\Clusters\CRM\Pages;

use App\Filament\Pages\AnalyticsBasePage;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Widgets\CRMStatsOverviewWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\DealStatusDistributionWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\LeadConversionTrendWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\LeadPipelineWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\RevenueForecastWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\SalesTeamPerformanceWidget;

class CRMAnalyticsPage extends AnalyticsBasePage
{
    protected static ?string $navigationLabel = 'Analytics';

    protected static ?string $title = 'CRM Analytics Dashboard';

    protected static ?string $slug = 'crm-analytics';

    protected static ?string $cluster = CRMCluster::class;

    protected static ?int $navigationSort = 1;

    // Temporarily disabled - focus on other tasks
    protected static bool $shouldRegisterNavigation = false;

    public function getWidgets(): array
    {
        return [
            // Row 1: Full-width overview (most important KPIs)
            CRMStatsOverviewWidget::class,

            // Row 2: Primary metrics (2 columns on desktop)
            LeadPipelineWidget::class,              // Left: Sales funnel
            RevenueForecastWidget::class,           // Right: Revenue projection

            // Row 3: Trends and analysis (2 columns on desktop)
            LeadConversionTrendWidget::class,       // Left: Historical trend
            SalesTeamPerformanceWidget::class,      // Right: Team comparison

            // Row 4: Distribution (centered single column)
            DealStatusDistributionWidget::class,    // Full width or centered
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
