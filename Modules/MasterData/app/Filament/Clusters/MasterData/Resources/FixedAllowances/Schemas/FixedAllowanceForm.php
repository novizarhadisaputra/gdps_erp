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
                Section::make('Allowance Definition')
                    ->description('Define fixed allowance components such as Base Salary or Fixed Transport Allowance.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Allowance Name')
                            ->placeholder('e.g. Gaji Pokok, Tunjangan Jabatan')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The official name of this allowance.'),
                        TextInput::make('code')
                            ->label('Allowance Code')
                            ->placeholder('e.g. FA-BASE, FA-POS')
                            ->required()
                            ->unique(FixedAllowance::class, 'code', ignoreRecord: true)
                            ->helperText('Unique short code for this allowance.'),
                    ])->columns(2),

                Section::make('Configuration & Rules')
                    ->description('Set financial and tax rules for this allowance.')
                    ->schema([
                        TextInput::make('default_amount')
                            ->label('Standard Amount')
                            ->numeric()
                            ->prefix('IDR')
                            ->placeholder('0.00')
                            ->default(0)
                            ->required()
                            ->helperText('Default monetary value for this allowance.'),
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
                    ->description('Manage visibility and default usage.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Inactive allowances cannot be selected for new employee costings.'),
                        Toggle::make('is_default')
                            ->label('Set as Default')
                            ->default(false)
                            ->helperText('If enabled, this allowance will be pre-added to new costing templates.'),
                    ])->columns(2),
            ]);
    }
}
