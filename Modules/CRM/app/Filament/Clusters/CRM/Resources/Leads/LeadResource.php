<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads;

use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\EditLead;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\LeadBoard;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ListLeads;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageContracts;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageCostingTemplates;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageGeneralInformations;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageManpowerTemplates;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageProfitabilityAnalyses;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageProjectInformations;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageProposals;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageSalesPlans;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ViewLead;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas\LeadForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas\LeadInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Tables\LeadsTable;
use Modules\CRM\Models\Lead;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = CRMCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFunnel;

    protected static ?int $navigationSort = 1;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Start;
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        $record = $page->getRecord();

        return [
            ...$page->generateNavigationItems([
                EditLead::class,
                ManageProjectInformations::class,
            ]),
            ...collect($page->generateNavigationItems([
                ManageSalesPlans::class,
            ]))->map(fn (NavigationItem $item) => $item
                ->group('Stage 1: Planning (Approach)')
                ->visible(fn () => in_array($record->status, [
                    LeadStatus::Approach,
                    LeadStatus::Proposal,
                    LeadStatus::Negotiation,
                    LeadStatus::Won,
                ])))->toArray(),
            ...collect($page->generateNavigationItems([
                ManageGeneralInformations::class,
                ManageCostingTemplates::class,
                ManageManpowerTemplates::class,
                ManageProfitabilityAnalyses::class,
            ]))->map(fn (NavigationItem $item) => $item
                ->group('Stage 1: Planning (Approach)')
                ->visible(fn () => in_array($record->status, [
                    LeadStatus::Approach,
                    LeadStatus::Proposal,
                    LeadStatus::Negotiation,
                    LeadStatus::Won,
                ])))->toArray(),
            ...collect($page->generateNavigationItems([
                ManageProposals::class,
            ]))->map(fn (NavigationItem $item) => $item
                ->group('Stage 2: Commercials (Proposal)')
                ->visible(fn () => in_array($record->status, [
                    LeadStatus::Proposal,
                    LeadStatus::Negotiation,
                    LeadStatus::Won,
                ]) && $record->profitabilityAnalyses()->where('status', 'approved')->exists()
                ))->toArray(),
            ...collect($page->generateNavigationItems([
                ManageContracts::class,
            ]))->map(fn (NavigationItem $item) => $item
                ->group('Stage 3: Contracting (Negotiation)')
                ->visible(fn () => in_array($record->status, [
                    LeadStatus::Negotiation,
                    LeadStatus::Won,
                ]) && $record->proposals()->where('status', ProposalStatus::Approved)->exists()
                ))->toArray(),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeadInfolist::configure($schema);
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
            'index' => LeadBoard::route('/'),
            'list' => ListLeads::route('/list'),
            'view' => ViewLead::route('/{record}'),
            'edit' => EditLead::route('/{record}/edit'),
            'sales-plans' => ManageSalesPlans::route('/{record}/sales-plans'),
            'general-informations' => ManageGeneralInformations::route('/{record}/general-informations'),
            'proposals' => ManageProposals::route('/{record}/proposals'),
            'manpower-costing' => ManageManpowerTemplates::route('/{record}/manpower-costing'),
            'profitability-analysis' => ManageProfitabilityAnalyses::route('/{record}/profitability-analysis'),
            'project-informations' => ManageProjectInformations::route('/{record}/project-informations'),
            'contracts' => ManageContracts::route('/{record}/contracts'),
            'tools-equipment-costing' => ManageCostingTemplates::route('/{record}/tools-equipment-costing'),
        ];
    }
}
