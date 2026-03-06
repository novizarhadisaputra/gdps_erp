<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\Pages\CreateNonFixedAllowance;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\Pages\EditNonFixedAllowance;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\Pages\ListNonFixedAllowances;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\Schemas\NonFixedAllowanceForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\Tables\NonFixedAllowancesTable;
use Modules\MasterData\Models\NonFixedAllowance;

class NonFixedAllowanceResource extends Resource
{
    protected static ?string $model = NonFixedAllowance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return NonFixedAllowanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NonFixedAllowancesTable::configure($table);
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
            'index' => ListNonFixedAllowances::route('/'),
            'create' => CreateNonFixedAllowance::route('/create'),
            'edit' => EditNonFixedAllowance::route('/{record}/edit'),
        ];
    }
}
