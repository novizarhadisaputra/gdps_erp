<?php

namespace Modules\MasterData\Filament\Resources\Clients;

use BackedEnum;
use Modules\MasterData\Models\Client;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Resources\Clients\Pages\CreateClient;
use Modules\MasterData\Filament\Resources\Clients\Pages\EditClient;
use Modules\MasterData\Filament\Resources\Clients\Pages\ListClients;
use Modules\MasterData\Filament\Resources\Clients\Schemas\ClientForm;
use Modules\MasterData\Filament\Resources\Clients\Tables\ClientsTable;

class ClientResource extends Resource
{
    protected static ?string $cluster = \Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster::class;

    protected static ?string $model = \Modules\MasterData\Models\Client::class;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
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
            'index' => ListClients::route('/'),
        ];
    }
}
