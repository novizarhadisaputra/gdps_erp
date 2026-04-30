<?php

namespace Modules\Project\Filament\Clusters\Project\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Filament\Clusters\Project\ProjectCluster;
use Modules\Project\Filament\Clusters\Project\Widgets\ProjectBudgetAnalysisWidget;
use Modules\Project\Filament\Clusters\Project\Widgets\ProjectsByStatusWidget;
use Modules\Project\Filament\Clusters\Project\Widgets\ProjectsByTypeWidget;
use Modules\Project\Filament\Clusters\Project\Widgets\ProjectStatsWidget;
use Modules\Project\Filament\Clusters\Project\Widgets\ProjectTimelineWidget;

class ProjectDashboard extends Page
{
    protected static ?string $slug = 'project-overview';

    protected static ?string $cluster = ProjectCluster::class;

    protected static ?string $title = 'Project Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 0;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    public function getWidgets(): array
    {
        return [
            ProjectStatsWidget::class,
            ProjectsByStatusWidget::class,
            ProjectsByTypeWidget::class,
            ProjectBudgetAnalysisWidget::class,
            ProjectTimelineWidget::class,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getWidgets())),
            ]);
    }
}
