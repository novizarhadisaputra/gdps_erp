<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Finance\Filament\Clusters\Finance\FinanceCluster;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Pages\ViewAccrueRevenue;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Schemas\AccrueRevenueForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Schemas\AccrueRevenueInfolist;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Tables\AccrueRevenueTable;
use Modules\Finance\Models\AccrueRevenue;

class AccrueRevenueResource extends Resource
{
    protected static ?string $model = AccrueRevenue::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?string $cluster = FinanceCluster::class;

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return AccrueRevenueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccrueRevenueTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccrueRevenueInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccrueRevenues::route('/'),
            'create' => Pages\CreateAccrueRevenue::route('/create'),
            'view' => ViewAccrueRevenue::route('/{record}'),
            'edit' => Pages\EditAccrueRevenue::route('/{record}/edit'),
        ];
    }
}
