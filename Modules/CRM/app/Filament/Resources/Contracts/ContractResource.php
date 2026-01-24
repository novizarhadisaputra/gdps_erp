<?php

namespace Modules\CRM\Filament\Resources\Contracts;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Resources\Contracts\Pages\CreateContract;
use Modules\CRM\Filament\Resources\Contracts\Pages\EditContract;
use Modules\CRM\Filament\Resources\Contracts\Pages\ListContracts;
use Modules\CRM\Filament\Resources\Contracts\Schemas\ContractForm;
use Modules\CRM\Filament\Resources\Contracts\Tables\ContractsTable;
use Modules\CRM\Models\Contract;

class ContractResource extends Resource
{
    protected static ?string $cluster = \Modules\CRM\Filament\Clusters\CRM\CRMCluster::class;

    protected static ?string $model = Contract::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractsTable::configure($table);
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
            'index' => ListContracts::route('/'),
        ];
    }
}
