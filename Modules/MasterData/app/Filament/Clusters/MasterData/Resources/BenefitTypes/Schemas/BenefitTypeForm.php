<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\BenefitType;

class BenefitTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Benefit Configuration')
                    ->description('Define the types of benefits available to employees and their accrual behavior.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Benefit Name')
                            ->placeholder('e.g. Health Insurance, Meal Allowance')
                            ->helperText('The descriptive name of the benefit type.')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Benefit Code')
                            ->placeholder('e.g. BEN-HEALTH, BEN-MEAL')
                            ->required()
                            ->unique(BenefitType::class, 'code', ignoreRecord: true)
                            ->helperText('Unique short code identifying this benefit.'),
                        Select::make('accrual_method')
                            ->label('Accrual Method')
                            ->options([
                                'monthly' => 'Monthly',
                                'one_time' => 'One Time',
                                'yearly' => 'Yearly',
                            ])
                            ->default('monthly')
                            ->placeholder('Select method')
                            ->helperText('Specifies how frequently this benefit is accrued or distributed.')
                            ->required(),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this benefit type.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Determines if this benefit can be assigned to employee groups.'),
                        Toggle::make('is_default')
                            ->label('Default Benefit')
                            ->default(false)
                            ->helperText('Sets this as the pre-selected option for new benefit assignments.'),
                    ])->columns(2),
            ]);
    }
}
