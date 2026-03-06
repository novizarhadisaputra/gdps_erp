<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Pages\CreateFixedAllowance;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Pages\EditFixedAllowance;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Pages\ListFixedAllowances;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Schemas\FixedAllowanceForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Tables\FixedAllowancesTable;
use Modules\MasterData\Models\FixedAllowance;

class FixedAllowanceResource extends Resource
{
    protected static ?string $model = FixedAllowance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FixedAllowanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FixedAllowancesTable::configure($table);
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
            'index' => ListFixedAllowances::route('/'),
            'create' => CreateFixedAllowance::route('/create'),
            'edit' => EditFixedAllowance::route('/{record}/edit'),
        ];
    }
}
