<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Pages\CreateGeneralInformation;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Pages\EditGeneralInformation;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Pages\ListGeneralInformation;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas\GeneralInformationForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Tables\GeneralInformationTable;

class GeneralInformationResource extends Resource
{
    protected static ?string $model = GeneralInformation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = CRMCluster::class;

    public static function form(Schema $schema): Schema
    {
        return GeneralInformationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeneralInformationTable::configure($table);
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
            'index' => ListGeneralInformation::route('/'),
            'create' => CreateGeneralInformation::route('/create'),
            'edit' => EditGeneralInformation::route('/{record}/edit'),
        ];
    }
}
