<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;

class ListProposals extends ListRecords
{
    use InteractsWithParentRecord;

    protected static string $resource = ProposalResource::class;

    public function getSubheading(): ?string
    {
        return 'Manage project proposals and client offerings.';
    }
}
