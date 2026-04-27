<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal;

use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages\CreateProposal;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages\EditProposal;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages\ListProposals;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages\ManageProposalRevisions;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages\SendProposal;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages\ViewProposal;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\Pages\ViewProposalRevision;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Schemas\ProposalForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Schemas\ProposalInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Tables\ProposalsTable;
use Modules\CRM\Models\Proposal;

class ProposalResource extends Resource
{
    protected static ?string $model = Proposal::class;

    protected static bool $isNested = true;

    protected static ?string $slug = 'proposals';

    protected static ?string $parentResource = LeadResource::class;
    
    protected static ?string $parentRouteParameterName = 'lead';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ProposalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProposalsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProposalInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewProposal::class,
            EditProposal::class,
            ManageProposalRevisions::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProposals::route('/'),
            'create' => CreateProposal::route('/create'),
            'view' => ViewProposal::route('/{record}'),
            'edit' => EditProposal::route('/{record}/edit'),
            'send' => SendProposal::route('/{record}/send'),
            'revisions' => ManageProposalRevisions::route('/{record}/revisions'),
            'view-revision' => ViewProposalRevision::route('/{record}/revisions/{relatedRecord}'),
        ];
    }
}
