<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\Pages\CreateThrBasisType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\Pages\EditThrBasisType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\Pages\ListThrBasisTypes;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\Pages\ViewThrBasisType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\Schemas\ThrBasisTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\Tables\ThrBasisTypesTable;
use Modules\MasterData\Models\ThrBasisType;

class ThrBasisTypeResource extends Resource
{
    protected static ?string $model = ThrBasisType::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Payroll & Benefits';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ThrBasisTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ThrBasisTypesTable::configure($table);
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
            'index' => ListThrBasisTypes::route('/'),
            'create' => CreateThrBasisType::route('/create'),
            'view' => ViewThrBasisType::route('/{record}'),
            'edit' => EditThrBasisType::route('/{record}/edit'),
        ];
    }
}
