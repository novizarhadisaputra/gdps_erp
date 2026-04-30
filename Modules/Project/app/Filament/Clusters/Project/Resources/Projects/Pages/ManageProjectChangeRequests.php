<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages;

use BackedEnum;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\ProjectChangeRequestResource;

class ManageProjectChangeRequests extends ManageRelatedRecords
{
    protected static string $resource = ProjectResource::class;

    protected static ?string $relatedResource = ProjectChangeRequestResource::class;

    protected static string $relationship = 'changeRequests';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?string $navigationLabel = 'Change Requests';

    protected static ?string $title = 'Project Change Requests';
}
