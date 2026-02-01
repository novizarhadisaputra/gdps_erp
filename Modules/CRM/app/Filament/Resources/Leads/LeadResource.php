<?php

namespace Modules\CRM\Filament\Resources\Leads;

use Modules\CRM\Filament\Resources\Leads\Pages;
use Modules\CRM\Models\Lead;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Filament\Resources\Leads\Schemas\LeadForm;
use Modules\CRM\Filament\Resources\Leads\Schemas\LeadInfolist;
use Modules\CRM\Filament\Resources\Leads\Tables\LeadsTable;
use Filament\Pages\Page;
use Filament\Pages\Enums\SubNavigationPosition;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = CRMCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFunnel;

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditLead::class,
            Pages\ManageGeneralInformations::class,
            Pages\ManageProposals::class,
            Pages\ManageProfitabilityAnalyses::class,
            Pages\ManageContracts::class,
            Pages\ManageProjectInformations::class,
        ]);
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
            'index' => Pages\ListLeads::route('/'),
            'kanban' => Pages\LeadBoard::route('/kanban'),
            'create' => Pages\CreateLead::route('/create'),
            'view' => Pages\ViewLead::route('/{record}'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
            'proposals' => Pages\ManageProposals::route('/{record}/proposals'),
            'general-informations' => Pages\ManageGeneralInformations::route('/{record}/general-informations'),
            'profitability-analyses' => Pages\ManageProfitabilityAnalyses::route('/{record}/profitability-analyses'),
            'project-informations' => Pages\ManageProjectInformations::route('/{record}/project-informations'),
            'contracts' => Pages\ManageContracts::route('/{record}/contracts'),
        ];
    }
}
