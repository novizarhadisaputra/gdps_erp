<?php

namespace Modules\MasterData\Filament\Resources\PaymentTerms;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Resources\PaymentTerms\Pages\CreatePaymentTerm;
use Modules\MasterData\Filament\Resources\PaymentTerms\Pages\EditPaymentTerm;
use Modules\MasterData\Filament\Resources\PaymentTerms\Pages\ListPaymentTerms;
use Modules\MasterData\Filament\Resources\PaymentTerms\Schemas\PaymentTermForm;
use Modules\MasterData\Filament\Resources\PaymentTerms\Tables\PaymentTermsTable;
use Modules\MasterData\Models\PaymentTerm;

class PaymentTermResource extends Resource
{
    protected static ?string $cluster = \Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster::class;

    protected static ?string $model = PaymentTerm::class;

    protected static ?int $navigationSort = 11;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
