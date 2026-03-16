<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource;

class ManageMinutesOfAgreements extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'minutesOfAgreements';

    protected static ?string $relatedResource = MinutesOfAgreementResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    public function getSubheading(): ?string
    {
        return 'Manage Minutes of Agreement (BA) for this lead.';
    }

    protected static ?string $title = 'Minutes of Agreement';

    public function form(Schema $schema): Schema
    {
        return MinutesOfAgreementResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return MinutesOfAgreementResource::table($table)
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->url(fn () => MinutesOfAgreementResource::getUrl('create', ['lead' => $this->getOwnerRecord()->id])),
            ]);
    }
}
