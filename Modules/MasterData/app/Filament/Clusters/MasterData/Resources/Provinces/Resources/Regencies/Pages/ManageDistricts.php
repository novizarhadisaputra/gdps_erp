<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Exception;
use Filament\Support\Icons\Heroicon;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\ProvinceResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\DistrictResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\RegencyResource;
use Modules\MasterData\Services\WilayahSyncService;

class ManageDistricts extends ManageRelatedRecords
{
    protected static string $resource = RegencyResource::class;

    protected static string $relationship = 'districts';

    protected static ?string $relatedResource = DistrictResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedHomeModern;

    public function getBreadcrumbs(): array
    {
        $regency = $this->getOwnerRecord();
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
        } catch (Exception $e) {
            $breadcrumbs[] = $regency->name;
        }

        $breadcrumbs[] = 'Districts';
        return $breadcrumbs;
    }

    public function getTitle(): string
    {
        return 'Districts in ' . $this->getOwnerRecord()->name;
    }

    public function getHeading(): string
    {
        return 'Districts in ' . $this->getOwnerRecord()->name;
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $regency = $this->getOwnerRecord();

        if ($regency->districts()->count() === 0) {
            app(WilayahSyncService::class)->syncDistricts($regency);
        }
    }

    public function form(Schema $schema): Schema
    {
        return DistrictResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return DistrictResource::table($table);
    }
}
