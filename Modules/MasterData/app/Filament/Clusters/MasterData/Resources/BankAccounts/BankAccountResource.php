<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Pages\CreateBankAccount;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Pages\EditBankAccount;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Pages\ListBankAccounts;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Pages\ViewBankAccount;
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
            'create' => CreateBankAccount::route('/create'),
            'view' => ViewBankAccount::route('/{record}'),
            'edit' => EditBankAccount::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Bank Account');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Bank Accounts');
    }

    public static function getNavigationLabel(): string
    {
        return __('Bank Accounts');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Finance & Accounting');
    }
}
