<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource;

class ManageGeneralInformations extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'generalInformations';

    protected static ?string $relatedResource = GeneralInformationResource::class;

    protected static ?string $title = 'General Information';

    public function form(Schema $schema): Schema
    {
        return GeneralInformationResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return GeneralInformationResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->schema(fn (Schema $schema) => GeneralInformationResource::form($schema)),
            ]);
    }
}
