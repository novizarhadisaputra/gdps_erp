<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BenefitTypes\Schemas;

use Filament\Schemas\Schema;

class BenefitTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('General Details')
                    ->description('Fill in the necessary configuration properties below.')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->placeholder('Enter Name...')
                            ->helperText('Brief and clear Name for this record.')
                            ->required(),
                        \Filament\Forms\Components\Select::make('accrual_method')
                            ->options(['monthly' => 'Monthly', 'one_time' => 'One Time', 'yearly' => 'Yearly'])
                            ->default('monthly')
                            ->label('Accrual Method')
                            ->placeholder('Select Accrual Method')
                            ->helperText('How often does this benefit accrue?'),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Status (Active / Inactive)')
                            ->helperText('Toggle on to make this record available in standard lists within the system.'),
                    ])->columns(2),
            ]);
    }
}
