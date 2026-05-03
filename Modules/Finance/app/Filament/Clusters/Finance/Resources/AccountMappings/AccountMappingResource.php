<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Finance\Filament\Clusters\Finance\FinanceCluster;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Schemas\AccountMappingForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Tables\AccountMappingsTable;
use Modules\Finance\Models\AccountMapping;

class AccountMappingResource extends Resource
{
    protected static ?string $model = AccountMapping::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $cluster = FinanceCluster::class;

    protected static ?int $navigationSort = 10;

    protected static \UnitEnum|string|null $navigationGroup = 'Configuration';

    public static function form(Schema $schema): Schema
    {
        return AccountMappingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountMappingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountMappings::route('/'),
            'create' => Pages\CreateAccountMapping::route('/create'),
            'edit' => Pages\EditAccountMapping::route('/{record}/edit'),
        ];
    }
}
