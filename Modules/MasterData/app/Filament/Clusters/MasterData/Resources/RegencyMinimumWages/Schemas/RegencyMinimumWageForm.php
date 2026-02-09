<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;

class RegencyMinimumWageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Select::make('project_area_id')
                ->label('Project Area')
                ->relationship('projectArea', 'name')
                ->createOptionForm(ProjectAreaForm::schema())
                ->createOptionAction(fn (Action $action) => $action->slideOver())
                ->required()
                ->searchable()
                ->preload(),
            TextInput::make('year')
                ->numeric()
                ->default(date('Y'))
                ->required(),
            TextInput::make('amount')
                ->label('Monthly Wage (UMK)')
                ->numeric()
                ->required(),
        ];
    }
}
