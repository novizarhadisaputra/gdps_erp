<?php

namespace Modules\CRM\Filament\Clusters\CRM\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Widgets\CRMStatsOverviewWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\MonthlyPerformanceTrendWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\ProductClusterChartWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\RevenueSegmentDonutWidget;

class ProjectReviewDashboard extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $cluster = CRMCluster::class;

    protected string $view = 'filament.pages.project-review-dashboard';

    protected static ?string $navigationLabel = 'Project Review';

    protected static ?string $title = 'Project Review Dashboard';

    protected static \UnitEnum|string|null $navigationGroup = 'Leads';

    protected static ?int $navigationSort = -10;

    protected function getHeaderWidgets(): array
    {
        return [
            CRMStatsOverviewWidget::class,
            RevenueSegmentDonutWidget::class,
            ProductClusterChartWidget::class,
            MonthlyPerformanceTrendWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
