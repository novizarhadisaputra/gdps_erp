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
                Section::make(__('Benefit Configuration'))
                    ->description(__('Define the types of benefits available to employees and their accrual behavior.'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Benefit Name'))
                            ->placeholder(__('e.g. Health Insurance, Meal Allowance'))
                            ->helperText(__('The descriptive name of the benefit type.'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label(__('Benefit Code'))
                            ->placeholder(__('e.g. BEN-HEALTH, BEN-MEAL'))
                            ->required()
                            ->unique(BenefitType::class, 'code', ignoreRecord: true)
                            ->helperText(__('Unique short code identifying this benefit.')),
                        Select::make('accrual_method')
                            ->label(__('Accrual Method'))
                            ->options([
                                'monthly' => __('Monthly'),
                                'one_time' => __('One Time'),
                                'yearly' => __('Yearly'),
                            ])
                            ->default('monthly')
                            ->placeholder(__('Select method'))
                            ->helperText(__('Specifies how frequently this benefit is accrued or distributed.'))
                            ->required(),
                    ])->columns(2),

                Section::make(__('Status & Defaults'))
                    ->description(__('Manage the availability and default status of this benefit type.'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active Status'))
                            ->default(true)
                            ->helperText(__('Determines if this benefit can be assigned to employee groups.')),
                        Toggle::make('is_default')
                            ->label(__('Default Benefit'))
                            ->default(false)
                            ->helperText(__('Sets this as the pre-selected option for new benefit assignments.')),
                    ])->columns(2),
            ]);
    }
}
