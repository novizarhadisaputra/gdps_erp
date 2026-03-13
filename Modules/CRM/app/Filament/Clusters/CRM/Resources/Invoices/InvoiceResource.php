<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Invoices;

use App\Models\Invoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\Invoices\Pages\CreateInvoice;
use Modules\CRM\Filament\Clusters\CRM\Resources\Invoices\Pages\EditInvoice;
use Modules\CRM\Filament\Clusters\CRM\Resources\Invoices\Pages\ListInvoices;
use Modules\CRM\Filament\Clusters\CRM\Resources\Invoices\Schemas\InvoiceForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Invoices\Tables\InvoicesTable;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = CRMCluster::class;

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
