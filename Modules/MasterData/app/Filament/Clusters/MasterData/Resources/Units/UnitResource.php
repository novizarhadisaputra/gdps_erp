<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Units;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Pages\CreateUnit;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Pages\EditUnit;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Pages\ListUnits;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Pages\ManageUnitPermissions;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Pages\ViewUnit;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Schemas\UnitForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Tables\UnitsTable;
use Modules\MasterData\Models\Unit;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|\UnitEnum|null $navigationGroup = 'Filament Shield';

    public static function form(Schema $schema): Schema
    {
        return UnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitsTable::configure($table);
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
            'index' => ListUnits::route('/'),
            'create' => CreateUnit::route('/create'),
            'view' => ViewUnit::route('/{record}'),
            'edit' => EditUnit::route('/{record}/edit'),
            'permissions' => ManageUnitPermissions::route('/{record}/permissions'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Unit');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Units');
    }

    public static function getNavigationLabel(): string
    {
        return __('Units');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-shield::filament-shield.nav.group');
    }
}
