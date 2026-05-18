<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Pages\CreatePaymentTerm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Pages\EditPaymentTerm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Pages\ListPaymentTerms;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Pages\ViewPaymentTerm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Schemas\PaymentTermForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Tables\PaymentTermsTable;
use Modules\MasterData\Models\PaymentTerm;

class PaymentTermResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = PaymentTerm::class;

    protected static ?int $navigationSort = 60;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales Master';

    public static function form(Schema $schema): Schema
    {
        return PaymentTermForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentTermsTable::configure($table);
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
            'index' => ListPaymentTerms::route('/'),
            'create' => CreatePaymentTerm::route('/create'),
            'view' => ViewPaymentTerm::route('/{record}'),
            'edit' => EditPaymentTerm::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Payment Term');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Payment Terms');
    }

    public static function getNavigationLabel(): string
    {
        return __('Payment Terms');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Sales Master');
    }
}
