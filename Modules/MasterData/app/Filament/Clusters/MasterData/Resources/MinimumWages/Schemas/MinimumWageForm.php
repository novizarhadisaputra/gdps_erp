<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Enums\MinimumWageType;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;

class MinimumWageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Minimum Wage Details')
                ->description('Specify the minimum wage for a specific province and project area.')
                ->schema([
                    TextInput::make('province')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g., DKI Jakarta')
                        ->helperText('Name of the province.'),
                    Select::make('type')
                        ->options(MinimumWageType::class)
                        ->required()
                        ->helperText('Type of minimum wage (Regency/City/Province).'),
                    Select::make('project_area_id')
                        ->label('Project Area')
                        ->relationship('projectArea', 'name')
                        ->createOptionForm(ProjectAreaForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->required()
                        ->searchable()
                        ->preload()
                        ->helperText('Area where this wage applies.'),
                    TextInput::make('year')
                        ->numeric()
                        ->default(date('Y'))
                        ->required()
                        ->helperText('Effective year for this wage.'),
                    TextInput::make('amount')
                        ->label('Monthly Wage Amount')
                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                        ->prefix('IDR ')
                        ->required()
                        ->helperText('Numerical amount of the monthly wage.'),
                ])->columns(2),

            Section::make('Status')
                ->description('Manage active status and default settings.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Determines if this wage setting is currently active.'),
                    Toggle::make('is_default')
                        ->label('Set as Default')
                        ->default(false)
                        ->helperText('If enabled, this will be the default wage for its category.'),
                ]),
        ];
    }
}
