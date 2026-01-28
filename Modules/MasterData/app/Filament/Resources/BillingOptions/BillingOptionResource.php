<?php

namespace Modules\MasterData\Filament\Resources\BillingOptions;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Resources\BillingOptions\Pages\ListBillingOptions;
use Modules\MasterData\Filament\Resources\BillingOptions\Schemas\BillingOptionForm;
use Modules\MasterData\Filament\Resources\BillingOptions\Tables\BillingOptionsTable;
use Modules\MasterData\Models\BillingOption;

class BillingOptionResource extends Resource
{
    protected static ?string $cluster = \Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster::class;

    protected static ?string $model = BillingOption::class;

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    public static function form(Schema $schema): Schema
    {
        return BillingOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BillingOptionsTable::configure($table);
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
            'index' => ListBillingOptions::route('/'),
        ];
    }
}
