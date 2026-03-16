<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\ProjectReviewResource;

class CreateProjectReview extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProjectReviewResource::class;
}
