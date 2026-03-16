<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\ProposalRevisionResource;

class ListProposalRevisions extends ListRecords
{
    use InteractsWithParentRecord;

    protected static string $resource = ProposalRevisionResource::class;

    public function getSubheading(): ?string
    {
        return 'View previous revisions of this proposal.';
    }
}
