<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\UnitResource;

class ManageUnitPermissions extends EditRecord
{
    use HasShieldFormComponents;

    protected static string $resource = UnitResource::class;

    protected static ?string $title = 'Manage Permissions';

    protected static ?string $navigationLabel = 'Permissions';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                static::getShieldFormComponents(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['permissions'] = $this->getRecord()->permissions->pluck('id')->toArray();

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $permissions = [];
        foreach ($data as $key => $permission) {
            $permissions = array_merge($permission, $data[$key]);
        }
        $record->syncPermissions($permissions);

        return $record;
    }
}
