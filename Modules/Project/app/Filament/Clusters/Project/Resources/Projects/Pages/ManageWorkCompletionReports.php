<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages;

use BackedEnum;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\WorkCompletionReportResource;

class ManageWorkCompletionReports extends ManageRelatedRecords
{
    protected static string $resource = ProjectResource::class;

    protected static ?string $relatedResource = WorkCompletionReportResource::class;

    protected static string $relationship = 'workCompletionReports';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Completion Reports';

    protected static ?string $title = 'Work Completion Reports';
}
