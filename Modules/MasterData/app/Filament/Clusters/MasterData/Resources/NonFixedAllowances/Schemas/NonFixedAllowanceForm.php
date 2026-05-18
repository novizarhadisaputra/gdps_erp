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
                Section::make(__('Allowance Definition'))
                    ->description(__('Define non-fixed allowance components such as Daily Transport or Shift Premiums.'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Allowance Name'))
                            ->placeholder(__('e.g. Transport Allowance, Lembur'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('The official name of this allowance.')),
                        TextInput::make('code')
                            ->label(__('Allowance Code'))
                            ->placeholder(__('e.g. NFA-TRANS, NFA-OT'))
                            ->required()
                            ->unique(\Modules\MasterData\Models\NonFixedAllowance::class, 'code', ignoreRecord: true)
                            ->helperText(__('Unique short code for this allowance.')),
                    ])->columns(2),

                Section::make(__('Calculation Configuration'))
                    ->description(__('Specify how this allowance is calculated and its financial rules.'))
                    ->schema([
                        Select::make('calculation_basis')
                            ->label(__('Calculation Basis'))
                            ->options([
                                'flat' => __('Flat / Fixed Amount'),
                                'per_day' => __('Per Day'),
                                'per_hour' => __('Per Hour'),
                                'per_output' => __('Per Output / Unit'),
                                'percentage' => __('Percentage (%)'),
                            ])
                            ->required()
                            ->native(false)
                            ->placeholder(__('Select basis'))
                            ->helperText(__('The method used to calculate the final allowance amount.')),
                        TextInput::make('default_amount')
                            ->label(__('Standard Rate/Amount'))
                            ->numeric()
                            ->prefix('IDR')
                            ->placeholder(__('0.00'))
                            ->default(0)
                            ->required()
                            ->helperText(__('The default monetary value or percentage rate for this allowance.')),
                        Toggle::make('is_bpjs_base')
                            ->label(__('Include in BPJS Base'))
                            ->default(false)
                            ->helperText(__('If enabled, this allowance will be included in the BPJS contribution base.')),
                        Toggle::make('is_taxable')
                            ->label(__('Taxable Component'))
                            ->default(true)
                            ->helperText(__('If enabled, this allowance will be subject to PPh 21 income tax.')),
                    ])->columns(2),

                Section::make(__('Status & Defaults'))
                    ->description(__('Manage the availability and default status of this allowance type.'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active Status'))
                            ->default(true)
                            ->helperText(__('Determines if this allowance can be assigned to employees.')),
                        Toggle::make('is_default')
                            ->label(__('Default Allowance'))
                            ->default(false)
                            ->helperText(__('Sets this as the pre-selected option for new allowance assignments.')),
                    ])->columns(2),
            ]);
    }
}
