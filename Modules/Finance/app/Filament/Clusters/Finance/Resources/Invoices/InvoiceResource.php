<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Finance\Filament\Clusters\Finance\FinanceCluster;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Pages\CreateInvoice;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Pages\EditInvoice;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Pages\ListInvoices;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Schemas\InvoiceForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Tables\InvoicesTable;
use Modules\Finance\Models\Invoice;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = FinanceCluster::class;

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
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
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }
}
