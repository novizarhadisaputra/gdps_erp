<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\CooperationAgreementResource;

class ManageCooperationAgreements extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'cooperationAgreements';

    protected static ?string $relatedResource = CooperationAgreementResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    public function getSubheading(): ?string
    {
        return 'Manage Cooperation Agreements (PKS) for this lead.';
    }

    protected static ?string $title = 'Cooperation Agreements';

    public function form(Schema $schema): Schema
    {
        return CooperationAgreementResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return CooperationAgreementResource::table($table)
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->url(fn () => CooperationAgreementResource::getUrl('create', ['lead' => $this->getOwnerRecord()->id])),
            ]);
    }
}
