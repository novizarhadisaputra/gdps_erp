<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation;

use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Pages\CreateGeneralInformation;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Pages\EditGeneralInformation;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Pages\ViewGeneralInformation;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Schemas\GeneralInformationForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Schemas\GeneralInformationInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Tables\GeneralInformationTable;
use Modules\CRM\Models\GeneralInformation;

class GeneralInformationResource extends Resource
{
    protected static ?string $cluster = CRMCluster::class;

    protected static ?string $model = GeneralInformation::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $parentResource = LeadResource::class;

    protected static ?string $parentRouteParameterName = 'lead';

    protected static ?string $recordTitleAttribute = 'scope_of_work';

    public static function form(Schema $schema): Schema
    {
        return GeneralInformationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeneralInformationTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GeneralInformationInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // public static function getRecordSubNavigation(Page $page): array
    // {
    //     return $page->generateNavigationItems([
    //         ViewGeneralInformation::class,
    //         EditGeneralInformation::class,
    //     ]);
    // }

    public static function getPages(): array
    {
        return [
            'create' => CreateGeneralInformation::route('/create'),
            'view' => ViewGeneralInformation::route('/{record}'),
            'edit' => EditGeneralInformation::route('/{record}/edit'),
        ];
    }
}
