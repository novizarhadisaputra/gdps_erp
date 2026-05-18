<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;

class ProjectAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(bool $includeParent = true): array
    {
        return [
            Section::make(__('Location Details'))
                ->description(__('Specify the geographic location and official regional data for this project area.'))
                ->schema([
                    Select::make('name')
                        ->label(__('Area Name'))
                        ->placeholder(__('Start typing to find location (e.g. Jakarta, Bali)...'))
                        ->required()
                        ->searchable()
                        ->options(function () {
                            return Province::limit(10)->pluck('name', 'name')
                                ->merge(Regency::limit(20)->pluck('name', 'name'))
                                ->toArray();
                        })
                        ->getSearchResultsUsing(function (string $search): array {
                            $provinces = Province::where('name', 'ILIKE', "%{$search}%")
                                ->limit(10)
                                ->pluck('name', 'name')
                                ->toArray();

                            $regencies = Regency::where('name', 'ILIKE', "%{$search}%")
                                ->limit(20)
                                ->pluck('name', 'name')
                                ->toArray();

                            return [
                                'Provinces' => $provinces,
                                'Regencies' => $regencies,
                            ];
                        })
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                $set('province_id', null);
                                $set('regency_id', null);
                                $set('province_name', null);
                                $set('regency_name', null);
                                $set('api_code', null);

                                return;
                            }

                            // Look for existing Regency first (most common level for project areas)
                            $regency = Regency::with('province')->where('name', $state)->first();
                            if ($regency) {
                                $set('province_id', $regency->province_id);
                                $set('regency_id', $regency->id);
                                $set('province_name', $regency->province?->name);
                                $set('regency_name', $regency->name);
                                $set('api_code', $regency->code);

                                return;
                            }

                            // Look for existing Province
                            $province = Province::where('name', $state)->first();
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
                        ->label(__('Province'))
                        ->readOnly()
                        ->placeholder(__('Auto-filled'))
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record?->province?->name),
                    TextInput::make('regency_name')
                        ->label(__('Regency / City'))
                        ->readOnly()
                        ->placeholder(__('Auto-filled'))
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record?->regency?->name),
                    TextInput::make('api_code')
                        ->label(__('Official Region Code'))
                        ->readOnly()
                        ->placeholder(__('Auto-filled'))
                        ->dehydrated()
                        ->helperText(__('BPS/Official geographic code.')),
                ]),

            Section::make(__('Internal Identification'))
                ->description(__('Provide internal coding and status for this project area.'))
                ->schema(array_filter([
                    TextInput::make('code')
                        ->label(__('Internal Area Code'))
                        ->unique(ProjectArea::class, 'code', ignoreRecord: true)
                        ->nullable()
                        ->placeholder(__('e.g. AREA-001'))
                        ->helperText(__('Internal identifier for project mapping.')),
                    $includeParent ? MorphToSelect::make('parentable')
                        ->label(__('Parent Area'))
                        ->types([
                            MorphToSelect\Type::make(ProjectArea::class)
                                ->titleAttribute('name')
                                ->label(__('Project Area')),
                        ])
                        ->searchable()
                        ->preload() : null,
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Determines if this area is currently available for project assignment.')),
                    Toggle::make('has_branches')
                        ->label(__('Has Branches'))
                        ->default(false)
                        ->helperText(__('Enable if this area contains sub-areas (nested structure).')),
                    Toggle::make('is_default')
                        ->label(__('Default Area'))
                        ->default(false)
                        ->helperText(__('Sets this as the primary default area for new projects.')),
                ])),

        ];
    }
}
