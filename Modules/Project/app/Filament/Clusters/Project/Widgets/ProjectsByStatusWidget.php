<?php

namespace Modules\Project\Filament\Clusters\Project\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\Project\Models\Project;

class ProjectsByStatusWidget extends ChartWidget
{
    protected ?string $heading = 'Projects by Status';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = Project::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Ensure all statuses are present even if count is 0
        $statuses = ['planning', 'active', 'completed', 'on hold', 'cancelled'];
        $finalData = [];
        foreach ($statuses as $status) {
            $finalData[$status] = $data[$status] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Projects',
                    'data' => array_values($finalData),
                    'backgroundColor' => [
                        '#94a3b8', // planning - slate
                        '#10b981', // active - emerald
                        '#3b82f6', // completed - blue
                        '#f59e0b', // on hold - amber
                        '#ef4444', // cancelled - red
                    ],
                ],
            ],
            'labels' => array_map('ucfirst', array_keys($finalData)),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
