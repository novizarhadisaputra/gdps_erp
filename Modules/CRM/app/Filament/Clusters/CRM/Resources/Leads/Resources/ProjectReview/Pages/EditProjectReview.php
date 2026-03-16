<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\ProjectReviewResource;

class EditProjectReview extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProjectReviewResource::class;
}
