<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Pages\CreateProvince;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Pages\EditProvince;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Pages\ListProvinces;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Pages\ManageRegencies;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Pages\ViewProvince;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Schemas\ProvinceForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Tables\ProvincesTable;
use Modules\MasterData\Models\Province;

class ProvinceResource extends Resource
{
    protected static ?string $model = Province::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|\UnitEnum|null $navigationGroup = 'Geography';

    protected static ?int $navigationSort = 100;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Start;
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewProvince::class,
            EditProvince::class,
            ManageRegencies::class,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return ProvinceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProvincesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProvinces::route('/'),
            'create' => CreateProvince::route('/create'),
            'view' => ViewProvince::route('/{record}'),
            'edit' => EditProvince::route('/{record}/edit'),
            'regencies' => ManageRegencies::route('/{record}/regencies'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Province');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Provinces');
    }

    public static function getNavigationLabel(): string
    {
        return __('Provinces');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Geography');
    }
}
