<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;

class ListProposals extends ListRecords
{
    protected static string $resource = ProposalResource::class;
}
