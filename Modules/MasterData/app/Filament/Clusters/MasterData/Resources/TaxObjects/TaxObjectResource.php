<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\Pages\CreateTaxObject;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\Pages\EditTaxObject;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\Pages\ListTaxObjects;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\Pages\ViewTaxObject;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\Schemas\TaxObjectForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\Tables\TaxObjectsTable;
use Modules\MasterData\Models\TaxObject;

class TaxObjectResource extends Resource
{
    protected static ?string $model = TaxObject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Taxation';

    protected static ?string $cluster = MasterDataCluster::class;

    public static function form(Schema $schema): Schema
    {
        return TaxObjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxObjectsTable::configure($table);
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
            'index' => ListTaxObjects::route('/'),
            'create' => CreateTaxObject::route('/create'),
            'view' => ViewTaxObject::route('/{record}'),
            'edit' => EditTaxObject::route('/{record}/edit'),
        ];
    }
}
