<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApiClients;

use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;

use App\Models\ApiClient;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ApiClients\Pages\ListApiClients;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ApiClients\Schemas\ApiClientForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ApiClients\Tables\ApiClientsTable;

class ApiClientResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = ApiClient::class;

    protected static ?int $navigationSort = 100; // Put it at the end

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return ApiClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApiClientsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApiClients::route('/'),
        ];
    }
}
