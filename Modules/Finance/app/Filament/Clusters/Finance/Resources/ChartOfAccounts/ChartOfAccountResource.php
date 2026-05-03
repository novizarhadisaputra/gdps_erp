<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Filament\Clusters\Finance\FinanceCluster;
use Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\Pages\ManageChartOfAccounts;
use Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\Schemas\ChartOfAccountForm;
use Modules\Finance\Models\ChartOfAccount;

class ChartOfAccountResource extends Resource
{
    protected static ?string $model = ChartOfAccount::class;

    protected static ?string $cluster = FinanceCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $navigationLabel = 'Chart of Accounts';

    protected static ?string $modelLabel = 'Chart of Account';

    protected static ?string $pluralModelLabel = 'Chart of Accounts';

    protected static \UnitEnum|string|null $navigationGroup = 'Setup';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return ChartOfAccountForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageChartOfAccounts::route('/'),
        ];
    }
}
