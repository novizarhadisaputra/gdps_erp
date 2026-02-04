<?php

namespace Modules\Project\Filament\Clusters\Project\Pages;

use App\Filament\Pages\AnalyticsBasePage;
use Modules\Project\Filament\Clusters\Project\ProjectCluster;
use Modules\Project\Filament\Clusters\Project\Widgets\ProjectBudgetAnalysisWidget;
use Modules\Project\Filament\Clusters\Project\Widgets\ProjectsByStatusWidget;
use Modules\Project\Filament\Clusters\Project\Widgets\ProjectsByTypeWidget;
use Modules\Project\Filament\Clusters\Project\Widgets\ProjectStatsWidget;
use Modules\Project\Filament\Clusters\Project\Widgets\ProjectTimelineWidget;

class ProjectAnalyticsPage extends AnalyticsBasePage
{
    protected static ?string $navigationLabel = 'Analytics';

    protected static ?string $title = 'Project Management Analytics';

    protected static ?string $cluster = ProjectCluster::class;

    protected static ?int $navigationSort = 1;

    // Temporarily disabled - focus on other tasks
    protected static bool $shouldRegisterNavigation = false;

    public function getWidgets(): array
    {
        return [
            // Row 1: Full-width overview (most important KPIs)
            ProjectStatsWidget::class,

            // Row 2: Distribution insights (2 columns)
            ProjectsByStatusWidget::class,          // Left: Status breakdown
            ProjectsByTypeWidget::class,            // Right: Type distribution

            // Row 3: Financial and timeline analysis (2 columns)
            ProjectBudgetAnalysisWidget::class,     // Left: Budget comparison
            ProjectTimelineWidget::class,           // Right: Gantt view
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
