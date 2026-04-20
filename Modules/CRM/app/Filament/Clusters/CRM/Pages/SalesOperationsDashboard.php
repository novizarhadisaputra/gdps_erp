<?php

namespace Modules\CRM\Filament\Clusters\CRM\Pages;

use App\Filament\Pages\AnalyticsBasePage;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Widgets\MonthlyRevenueTrendWidget;
use Modules\CRM\Filament\Clusters\CRM\Widgets\PerformanceOverviewWidget;

class SalesOperationsDashboard extends AnalyticsBasePage
{
    protected static ?string $title = 'Sales Operations Dashboard';

    protected static ?string $navigationLabel = 'SO Dashboard';

    protected static ?string $cluster = CRMCluster::class;

    protected static ?string $slug = 'sales-operations-dashboard';

    protected static ?int $navigationSort = 0;

    protected function getHeaderWidgets(): array
    {
        return [
            PerformanceOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            MonthlyRevenueTrendWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 1;
    }
}
