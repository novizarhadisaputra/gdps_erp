<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\ProvinceResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Pages\CreateRegency;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Pages\EditRegency;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Pages\ListRegencies;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Pages\ManageDistricts;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Pages\ViewRegency;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Schemas\RegencyForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Tables\RegenciesTable;
use Modules\MasterData\Models\Regency;

class RegencyResource extends Resource
{
    protected static ?string $model = Regency::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = ProvinceResource::class;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Start;
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewRegency::class,
            EditRegency::class,
            ManageDistricts::class,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return RegencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegenciesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegencies::route('/'),
            'create' => CreateRegency::route('/create'),
            'view' => ViewRegency::route('/{record}'),
            'edit' => EditRegency::route('/{record}/edit'),
            'districts' => ManageDistricts::route('/{record}/districts'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Regency');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Regencies');
    }

    public static function getNavigationLabel(): string
    {
        return __('Regencies');
    }
}
