<?php

namespace Modules\CRM\Filament\Clusters\CRM\Pages;

use Filament\Pages\Page;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;

class ProjectReviewDashboard extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $cluster = CRMCluster::class;

    protected string $view = 'filament.pages.project-review-dashboard';

    protected static ?string $navigationLabel = 'Project Review';

    protected static ?string $title = 'Project Review Dashboard';

    protected static \UnitEnum|string|null $navigationGroup = 'Leads';

    protected static ?int $navigationSort = -10;

    protected function getHeaderWidgets(): array
    {
        return [
            \Modules\CRM\Filament\Widgets\ProjectReviewWaitingWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
