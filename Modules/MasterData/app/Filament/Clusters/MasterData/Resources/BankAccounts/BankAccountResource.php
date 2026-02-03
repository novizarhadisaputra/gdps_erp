<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts;

use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Pages\ListBankAccounts;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Schemas\BankAccountForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Tables\BankAccountsTable;
use Modules\MasterData\Models\BankAccount;

class BankAccountResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = BankAccount::class;

    protected static ?int $navigationSort = 5; // Adjusted sort order

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Business';

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
