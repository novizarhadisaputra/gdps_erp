<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Filament\Forms\Components\Hidden;

class ProjectAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            TextInput::make('name')
                ->label('Area Name')
                ->placeholder('Start typing or choose from suggestions...')
                ->required()
                ->maxLength(255)
                ->datalist(function () {
                    return Province::pluck('name')
                        ->merge(Regency::pluck('name'))
                        ->unique()
                        ->sort()
                        ->toArray();
                })
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, Set $set) {
                    if (!$state) {
                        $set('province_id', null);
                        $set('regency_id', null);
                        $set('province_name', null);
                        $set('regency_name', null);
                        $set('api_code', null);
                        return;
                    }

                    // Look for existing Regency first (most common level for project areas)
                    $regency = Regency::with('province')->where('name', 'ILIKE', "%{$state}%")->first();
                    if ($regency) {
                        $set('province_id', $regency->province_id);
                        $set('regency_id', $regency->id);
                        $set('province_name', $regency->province?->name);
                        $set('regency_name', $regency->name);
                        $set('api_code', $regency->code);
                        return;
                    }

                    // Look for existing Province
                    $province = Province::where('name', 'ILIKE', "%{$state}%")->first();
                    if ($province) {
                        $set('province_id', $province->id);
                        $set('regency_id', null);
                        $set('province_name', $province->name);
                        $set('regency_name', null);
                        $set('api_code', $province->code);
                        return;
                    }

                    // Clear geographic associations if name doesn't match official data
                    $set('province_id', null);
                    $set('regency_id', null);
                    $set('province_name', null);
                    $set('regency_name', null);
                    $set('api_code', null);
                })
                ->columnSpanFull(),
            Hidden::make('province_id'),
            Hidden::make('regency_id'),
            TextInput::make('province_name')
                ->label('Province')
                ->readOnly()
                ->dehydrated(false)
                ->formatStateUsing(fn ($record) => $record?->province?->name),
            TextInput::make('regency_name')
                ->label('Regency / City')
                ->readOnly()
                ->dehydrated(false)
                ->formatStateUsing(fn ($record) => $record?->regency?->name),
            TextInput::make('api_code')
                ->label('Region Code (Official)')
                ->readOnly()
                ->placeholder('Auto-filled from search')
                ->dehydrated(),
            TextInput::make('code')
                ->label('Internal Code')
                ->unique(ProjectArea::class, 'code', ignoreRecord: true)
                ->nullable()
                ->placeholder('e.g. PAR-001')
                ->hint('Leave empty to auto-generate based on name.'),
            Toggle::make('is_active')
                ->label('Active Status')
                ->default(true),
        ];
    }
}
