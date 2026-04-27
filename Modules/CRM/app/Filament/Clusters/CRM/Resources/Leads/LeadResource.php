<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads;

use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Enums\MoAStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\EditLead;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\LeadBoard;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ListLeads;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageCooperationAgreements;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageCostingTemplates;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageGeneralInformations;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageManpowerTemplates;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageMinutesOfAgreements;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageProfitabilityAnalyses;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageProjectInformations;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageProjectReviews;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageProposals;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManagePurchaseOrders;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageSalesPlans;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageWorkOrders;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ViewLead;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas\LeadForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas\LeadInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Tables\LeadsTable;
use Modules\CRM\Models\Lead;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = CRMCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedFunnel;

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
                ManageProjectReviews::class,
            ]),
            ...collect($page->generateNavigationItems([
                ManageSalesPlans::class,
            ]))->map(fn (NavigationItem $item) => $item
                ->group('Stage 1: Planning (Approach)')
                ->visible(fn () => in_array($record->status, [
                    LeadStatus::Approach,
                    LeadStatus::Proposal,
                    LeadStatus::Negotiation,
                    LeadStatus::Contract,
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
                    LeadStatus::Contract,
                    LeadStatus::Won,
                ])))->toArray(),
            ...collect($page->generateNavigationItems([
                ManageProposals::class,
            ]))->map(fn (NavigationItem $item) => $item
                ->group('Stage 2: Commercials (Proposal)')
                ->visible(fn () => in_array($record->status, [
                    LeadStatus::Approach,
                    LeadStatus::Proposal,
                    LeadStatus::Negotiation,
                    LeadStatus::Contract,
                    LeadStatus::Won,
                ]) && ($record->profitabilityAnalyses()->whereIn('status', [ProfitabilityAnalysisStatus::Approved, ProfitabilityAnalysisStatus::Converted])->exists() || $record->profitabilityAnalyses()->where('is_margin_approved', true)->exists())
                ))->toArray(),
            ...collect($page->generateNavigationItems([
                ManageMinutesOfAgreements::class,
            ]))->map(fn (NavigationItem $item) => $item
                ->group('Stage 3: Contracting (Negotiation)')
                ->visible(fn () => in_array($record->status, [
                    LeadStatus::Negotiation,
                    LeadStatus::Contract,
                    LeadStatus::Won,
                ]) && $record->proposals()->where('status', ProposalStatus::Approved)->exists()
                ))->toArray(),
            ...collect($page->generateNavigationItems([
                ManagePurchaseOrders::class,
                ManageWorkOrders::class,
                ManageCooperationAgreements::class,
            ]))->map(fn (NavigationItem $item) => $item
                ->group('Stage 3: Contracting (Negotiation)')
                ->visible(fn () => in_array($record->status, [
                    LeadStatus::Negotiation,
                    LeadStatus::Contract,
                    LeadStatus::Won,
                ]) && $record->minutesOfAgreements()->where('status', MoAStatus::Approved)->exists()
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
            'project-reviews' => ManageProjectReviews::route('/{record}/project-reviews'),
            'sales-plans' => ManageSalesPlans::route('/{record}/sales-plans'),
            'general-informations' => ManageGeneralInformations::route('/{record}/general-informations'),
            'proposals' => ManageProposals::route('/{record}/proposals'),
            'manpower-costing' => ManageManpowerTemplates::route('/{record}/manpower-costing'),
            'profitability-analysis' => ManageProfitabilityAnalyses::route('/{record}/profitability-analysis'),
            'project-informations' => ManageProjectInformations::route('/{record}/project-informations'),
            'purchase-orders' => ManagePurchaseOrders::route('/{record}/purchase-orders'),
            'work-orders' => ManageWorkOrders::route('/{record}/work-orders'),
            'cooperation-agreements' => ManageCooperationAgreements::route('/{record}/cooperation-agreements'),
            'tools-equipment-costing' => ManageCostingTemplates::route('/{record}/tools-equipment-costing'),
            'minutes-of-agreements' => ManageMinutesOfAgreements::route('/{record}/minutes-of-agreements'),
        ];
    }
}
