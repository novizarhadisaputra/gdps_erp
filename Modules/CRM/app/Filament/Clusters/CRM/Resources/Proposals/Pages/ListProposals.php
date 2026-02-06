<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\ProposalResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\Schemas\ProposalForm;

class ListProposals extends ListRecords
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->schema(fn (Schema $schema) => ProposalForm::configure($schema)),
        ];
    }
}
