<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\Pages\CreateContractType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\Pages\EditContractType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\Pages\ListContractTypes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\Schemas\ContractTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\Tables\ContractTypesTable;
use Modules\MasterData\Models\ContractType;

class ContractTypeResource extends Resource
{
    protected static ?string $model = ContractType::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ContractTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractTypesTable::configure($table);
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
            'index' => ListContractTypes::route('/'),
            'create' => CreateContractType::route('/create'),
            'edit' => EditContractType::route('/{record}/edit'),
        ];
    }
}
