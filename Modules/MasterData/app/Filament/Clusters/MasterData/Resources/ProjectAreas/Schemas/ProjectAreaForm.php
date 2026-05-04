<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;
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
            Section::make('Location Details')
                ->description('Specify the geographic location and official regional data for this project area.')
                ->schema([
                    TextInput::make('name')
                        ->label('Area Name')
                        ->placeholder('Start typing to find location (e.g. Jakarta, Bali)...')
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
                            if (! $state) {
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
                        ->placeholder('Auto-filled')
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record?->province?->name),
                    TextInput::make('regency_name')
                        ->label('Regency / City')
                        ->readOnly()
                        ->placeholder('Auto-filled')
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record?->regency?->name),
                    TextInput::make('api_code')
                        ->label('Official Region Code')
                        ->readOnly()
                        ->placeholder('Auto-filled')
                        ->dehydrated()
                        ->helperText('BPS/Official geographic code.'),
                ]),

            Section::make('Internal Identification')
                ->description('Provide internal coding and status for this project area.')
                ->schema([
                    MorphToSelect::make('parentable')
                        ->label('Parent Entity')
                        ->types([
                            MorphToSelect\Type::make(Customer::class)
                                ->titleAttribute('name')
                                ->label('Customer'),
                            MorphToSelect\Type::make(ProjectArea::class)
                                ->titleAttribute('name')
                                ->label('Project Area'),
                        ])
                        ->searchable()
                        ->preload()
                        ->visible($includeParent),
                    TextInput::make('code')
                        ->label('Internal Area Code')
                        ->unique(ProjectArea::class, 'code', ignoreRecord: true)
                        ->nullable()
                        ->placeholder('e.g. AREA-001')
                        ->helperText('Internal identifier for project mapping.'),
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Determines if this area is currently available for project assignment.'),
                    Toggle::make('has_branches')
                        ->label('Has Branches')
                        ->default(false)
                        ->helperText('Enable if this area contains sub-areas (nested structure).'),
                    Toggle::make('is_default')
                        ->label('Default Area')
                        ->default(false)
                        ->helperText('Sets this as the primary default area for new projects.'),
                ]),

        ];
    }
}
