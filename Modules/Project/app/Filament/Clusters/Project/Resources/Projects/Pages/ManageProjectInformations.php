<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectInformations\ProjectInformationResource;

class ManageProjectInformations extends ManageRelatedRecords
{
    protected static string $resource = ProjectResource::class;

    protected static ?string $relatedResource = ProjectInformationResource::class;

    protected static string $relationship = 'information';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $navigationLabel = 'Project Info';

    protected static ?string $title = 'Project Information';
}
