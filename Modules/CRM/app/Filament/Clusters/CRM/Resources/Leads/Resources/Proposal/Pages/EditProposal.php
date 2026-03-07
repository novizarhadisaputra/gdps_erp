<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;

class EditProposal extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProposalResource::class;
}
