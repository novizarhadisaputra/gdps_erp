<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\Pages\CreatePartnerFeeType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\Pages\EditPartnerFeeType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\Pages\ListPartnerFeeTypes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\Schemas\PartnerFeeTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\Tables\PartnerFeeTypesTable;
use Modules\MasterData\Models\PartnerFeeType;

class PartnerFeeTypeResource extends Resource
{
    protected static ?string $model = PartnerFeeType::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PartnerFeeTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerFeeTypesTable::configure($table);
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
            'index' => ListPartnerFeeTypes::route('/'),
            'create' => CreatePartnerFeeType::route('/create'),
            'edit' => EditPartnerFeeType::route('/{record}/edit'),
        ];
    }
}
