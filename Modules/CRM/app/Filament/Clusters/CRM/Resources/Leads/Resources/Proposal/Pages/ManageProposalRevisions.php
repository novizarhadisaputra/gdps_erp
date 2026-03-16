<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\ProposalRevisionResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\Tables\ProposalRevisionsTable;

class ManageProposalRevisions extends ManageRelatedRecords
{
    protected static string $resource = ProposalResource::class;

    protected static string $relationship = 'revisions';

    protected static ?string $relatedResource = ProposalRevisionResource::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Revision History';

    public function getSubheading(): ?string
    {
        return 'View and manage previous versions of this proposal.';
    }

    public function table(Table $table): Table
    {
        return ProposalRevisionsTable::configure($table);
    }
}
