<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision;

use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\Pages\AuditProposalRevision;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\Pages\ViewProposalRevision;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\Schemas\ProposalRevisionForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\Tables\ProposalRevisionsTable;
use Modules\CRM\Models\ProposalRevision;

class ProposalRevisionResource extends Resource
{
    protected static ?string $model = ProposalRevision::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProposalResource::class;

    protected static ?string $navigationLabel = 'Revision History';

    protected static ?string $slug = 'revisions';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClock;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewProposalRevision::class,
            AuditProposalRevision::class,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return ProposalRevisionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProposalRevisionsTable::configure($table);
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
            'view' => ViewProposalRevision::route('/{record}'),
            'audit' => AuditProposalRevision::route('/{record}/audit'),
        ];
    }
}
