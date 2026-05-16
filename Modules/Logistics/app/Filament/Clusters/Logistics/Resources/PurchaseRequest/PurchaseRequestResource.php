<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Logistics\Filament\Clusters\Logistics\LogisticsCluster;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Schemas\PurchaseRequestForm;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Tables\PurchaseRequestsTable;
use Modules\Logistics\Models\PurchaseRequest;

class PurchaseRequestResource extends Resource
{
    protected static ?string $cluster = LogisticsCluster::class;

    protected static ?string $slug = 'purchase-requests';

    protected static ?string $model = PurchaseRequest::class;

    protected static ?int $navigationSort = 1;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function form(Schema $schema): Schema
    {
        return PurchaseRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Pages\ListPurchaseRequests::route('/'),
            'create' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Pages\CreatePurchaseRequest::route('/create'),
            'view' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Pages\ViewPurchaseRequest::route('/{record}'),
            'edit' => \Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Pages\EditPurchaseRequest::route('/{record}/edit'),
        ];
    }
}
