<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Pages;

use Exception;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\ProvinceResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\RegencyResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\DistrictResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\VillageResource;
use Modules\MasterData\Services\WilayahSyncService;

class ManageVillages extends ManageRelatedRecords
{
    protected static string $resource = DistrictResource::class;

    protected static string $relationship = 'villages';

    protected static ?string $relatedResource = VillageResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedHome;

    public function getBreadcrumbs(): array
    {
        $district = $this->getOwnerRecord();
        $regency = $district->regency;
        $province = $regency->province;

        $breadcrumbs = [
            ProvinceResource::getUrl('index') => 'Provinces',
            ProvinceResource::getUrl('edit', ['record' => $province->id]) => $province->name,
            ProvinceResource::getUrl('regencies', ['record' => $province->id]) => 'Regencies',
        ];

        try {
            $regencyUrl = RegencyResource::getUrl('edit', [
                'parent' => $province->id,
                'record' => $regency->id,
            ]);
            $breadcrumbs[$regencyUrl] = $regency->name;
            $districtsUrl = RegencyResource::getUrl('districts', [
                'parent' => $province->id,
                'record' => $regency->id,
            ]);
            $breadcrumbs[$districtsUrl] = 'Districts';
        } catch (Exception $e) {
            $breadcrumbs[] = $regency->name;
            $breadcrumbs[] = 'Districts';
        }

        try {
            $districtUrl = DistrictResource::getUrl('edit', [
                'parent' => $regency->id,
                'record' => $district->id,
            ]);
            $breadcrumbs[$districtUrl] = $district->name;
        } catch (Exception $e) {
            $breadcrumbs[] = $district->name;
        }

        $breadcrumbs[] = 'Villages';

        return $breadcrumbs;
    }

    public function getTitle(): string
    {
        return 'Villages in '.$this->getOwnerRecord()->name;
    }

    public function getHeading(): string
    {
        return 'Villages in '.$this->getOwnerRecord()->name;
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $district = $this->getOwnerRecord();

        if ($district->villages()->count() === 0) {
            app(WilayahSyncService::class)->syncVillages($district);
        }
    }

    public function form(Schema $schema): Schema
    {
        return VillageResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return VillageResource::table($table);
    }
}
