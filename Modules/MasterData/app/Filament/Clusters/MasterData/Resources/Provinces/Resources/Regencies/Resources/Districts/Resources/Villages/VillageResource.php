<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\DistrictResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\Pages\CreateVillage;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\Pages\EditVillage;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\Pages\ListVillages;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\Pages\ViewVillage;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\Schemas\VillageForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\Tables\VillagesTable;
use Modules\MasterData\Models\Village;

class VillageResource extends Resource
{
    protected static ?string $model = Village::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = DistrictResource::class;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Start;
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewVillage::class,
            EditVillage::class,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return VillageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VillagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVillages::route('/'),
            'create' => CreateVillage::route('/create'),
            'view' => ViewVillage::route('/{record}'),
            'edit' => EditVillage::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Village');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Villages');
    }

    public static function getNavigationLabel(): string
    {
        return __('Villages');
    }
}
