<?php

namespace Modules\CRM\Filament\Resources\Proposals\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Resources\Proposals\ProposalResource;

class CreateProposal extends CreateRecord
{
    protected static string $resource = ProposalResource::class;
}
