<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms;

use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Pages\ListPaymentTerms;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Schemas\PaymentTermForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Tables\PaymentTermsTable;
use Modules\MasterData\Models\PaymentTerm;

class PaymentTermResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = PaymentTerm::class;

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

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
        ];
    }
}
