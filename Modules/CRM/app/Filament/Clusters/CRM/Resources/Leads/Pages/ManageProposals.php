<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;

class ManageProposals extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'proposals';

    protected static ?string $relatedResource = ProposalResource::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $title = 'Lead Proposals';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return ProposalResource::form($schema);
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return ProposalResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->schema(fn (\Filament\Schemas\Schema $schema) => ProposalResource::form($schema)),
            ]);
    }
}
