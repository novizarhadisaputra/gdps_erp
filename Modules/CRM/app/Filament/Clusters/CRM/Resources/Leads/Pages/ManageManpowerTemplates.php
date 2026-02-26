<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\ManpowerTemplateResource;

class ManageManpowerTemplates extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'manpowerTemplates';

    protected static ?string $relatedResource = ManpowerTemplateResource::class;

    protected static ?string $title = 'Manpower Templates';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return ManpowerTemplateResource::form($schema);
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return ManpowerTemplateResource::table($table)
            ->headerActions([
                CreateAction::make()
                    ->schema(fn (\Filament\Schemas\Schema $schema) => ManpowerTemplateResource::form($schema)),
            ]);
    }
}
