<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Schemas\ContactRoleForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Tables\ContactRolesTable;
use Modules\MasterData\Models\ContactRole;

class ContactRoleResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = ContactRole::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|\UnitEnum|null $navigationGroup = 'Partners & Relations';

    protected static ?int $navigationSort = 140;

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

    public static function getModelLabel(): string
    {
        return __('Contact Role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Contact Roles');
    }

    public static function getNavigationLabel(): string
    {
        return __('Contact Roles');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Partners & Relations');
    }
}
