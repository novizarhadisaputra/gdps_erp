<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\ProvinceResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\RegencyResource;
use Modules\MasterData\Services\WilayahSyncService;

class ManageRegencies extends ManageRelatedRecords
{
    protected static string $resource = ProvinceResource::class;

    protected static string $relationship = 'regencies';

    protected static ?string $relatedResource = RegencyResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public function getBreadcrumbs(): array
    {
        $province = $this->getOwnerRecord();

        return [
            ProvinceResource::getUrl('index') => 'Provinces',
            ProvinceResource::getUrl('edit', ['record' => $province->id]) => $province->name,
            'Regencies',
        ];
    }

    public function getTitle(): string
    {
        return 'Regencies in '.$this->getOwnerRecord()->name;
    }

    public function getHeading(): string
    {
        return 'Regencies in '.$this->getOwnerRecord()->name;
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $province = $this->getOwnerRecord();

        if ($province->regencies()->count() === 0) {
            app(WilayahSyncService::class)->syncRegencies($province);
        }
    }

    public function form(Schema $schema): Schema
    {
        return RegencyResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return RegencyResource::table($table);
    }
}
