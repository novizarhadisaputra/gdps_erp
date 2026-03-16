<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\ProposalRevisionResource;

class ViewProposalRevision extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProposalRevisionResource::class;
}
