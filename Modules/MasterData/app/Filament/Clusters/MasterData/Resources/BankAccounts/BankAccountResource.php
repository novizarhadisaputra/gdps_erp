<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts;

use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Pages\ListBankAccounts;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Schemas\BankAccountForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Tables\BankAccountsTable;
use Modules\MasterData\Models\BankAccount;

class BankAccountResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = BankAccount::class;

    protected static ?int $navigationSort = 4;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance & Accounting';

    public static function form(Schema $schema): Schema
    {
        return BankAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankAccountsTable::configure($table);
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
            'index' => ListBankAccounts::route('/'),
        ];
    }
}
