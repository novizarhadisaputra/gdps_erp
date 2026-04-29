<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NonFixedAllowanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Allowance Definition')
                    ->description('Define non-fixed allowance components such as Daily Transport or Shift Premiums.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Allowance Name')
                            ->placeholder('e.g. Transport Allowance, Lembur')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The official name of this allowance.'),
                        TextInput::make('code')
                            ->label('Allowance Code')
                            ->placeholder('e.g. NFA-TRANS, NFA-OT')
                            ->required()
                            ->unique(\Modules\MasterData\Models\NonFixedAllowance::class, 'code', ignoreRecord: true)
                            ->helperText('Unique short code for this allowance.'),
                    ])->columns(2),

                Section::make('Calculation Configuration')
                    ->description('Specify how this allowance is calculated and its financial rules.')
                    ->schema([
                        Select::make('calculation_basis')
                            ->label('Calculation Basis')
                            ->options([
                                'flat' => 'Flat / Fixed Amount',
                                'per_day' => 'Per Day',
                                'per_hour' => 'Per Hour',
                                'per_output' => 'Per Output / Unit',
                                'percentage' => 'Percentage (%)',
                            ])
                            ->required()
                            ->native(false)
                            ->placeholder('Select basis')
                            ->helperText('The method used to calculate the final allowance amount.'),
                        TextInput::make('default_amount')
                            ->label('Standard Rate/Amount')
                            ->numeric()
                            ->prefix('IDR')
                            ->placeholder('0.00')
                            ->default(0)
                            ->required()
                            ->helperText('The default monetary value or percentage rate for this allowance.'),
                        Toggle::make('is_bpjs_base')
                            ->label('Include in BPJS Base')
                            ->default(false)
                            ->helperText('If enabled, this allowance will be included in the BPJS contribution base.'),
                        Toggle::make('is_taxable')
                            ->label('Taxable Component')
                            ->default(true)
                            ->helperText('If enabled, this allowance will be subject to PPh 21 income tax.'),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this allowance type.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Determines if this allowance can be assigned to employees.'),
                        Toggle::make('is_default')
                            ->label('Default Allowance')
                            ->default(false)
                            ->helperText('Sets this as the pre-selected option for new allowance assignments.'),
                    ])->columns(2),
            ]);
    }
}
