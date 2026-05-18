<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\FixedAllowance;

class FixedAllowanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Allowance Definition'))
                    ->description(__('Define fixed allowance components such as Base Salary or Fixed Transport Allowance.'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Allowance Name'))
                            ->placeholder(__('e.g. Gaji Pokok, Tunjangan Jabatan'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('The official name of this allowance.')),
                        TextInput::make('code')
                            ->label(__('Allowance Code'))
                            ->placeholder(__('e.g. FA-BASE, FA-POS'))
                            ->required()
                            ->unique(FixedAllowance::class, 'code', ignoreRecord: true)
                            ->helperText(__('Unique short code for this allowance.')),
                    ])->columns(2),

                Section::make(__('Configuration & Rules'))
                    ->description(__('Set financial and tax rules for this allowance.'))
                    ->schema([
                        TextInput::make('default_amount')
                            ->label(__('Standard Amount'))
                            ->numeric()
                            ->prefix('IDR')
                            ->placeholder(__('0.00'))
                            ->default(0)
                            ->required()
                            ->helperText(__('Default monetary value for this allowance.')),
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
                    ->description(__('Manage visibility and default usage.'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active Status'))
                            ->default(true)
                            ->helperText(__('Inactive allowances cannot be selected for new employee costings.')),
                        Toggle::make('is_default')
                            ->label(__('Set as Default'))
                            ->default(false)
                            ->helperText(__('If enabled, this allowance will be pre-added to new costing templates.')),
                    ])->columns(2),
            ]);
    }
}
