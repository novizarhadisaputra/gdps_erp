<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles;

use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Schemas\ContactRoleForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Tables\ContactRolesTable;
use Modules\MasterData\Models\ContactRole;

class ContactRoleResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;
    protected static ?string $model = ContactRole::class;


    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|\UnitEnum|null $navigationGroup = 'Reference Data';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return ContactRoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactRolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageContactRoles::route('/'),
        ];
    }
}
