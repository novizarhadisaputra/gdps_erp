<?php

namespace Modules\CRM\Filament\Resources\Proposals;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Resources\Proposals\Pages\CreateProposal;
use Modules\CRM\Filament\Resources\Proposals\Pages\EditProposal;
use Modules\CRM\Filament\Resources\Proposals\Pages\ListProposals;
use Modules\CRM\Filament\Resources\Proposals\Schemas\ProposalForm;
use Modules\CRM\Filament\Resources\Proposals\Tables\ProposalsTable;
use Modules\CRM\Models\Proposal;

class ProposalResource extends Resource
{
    protected static ?string $cluster = \Modules\CRM\Filament\Clusters\CRM\CRMCluster::class;

    protected static ?string $model = Proposal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProposalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProposalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProposals::route('/'),
        ];
    }
}
