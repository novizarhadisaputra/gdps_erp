<?php

namespace Modules\CRM\Filament\Resources\Contracts\Schemas;

use Filament\Schemas\Schema;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('client_id')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                \Filament\Forms\Components\Select::make('proposal_id')
                    ->relationship('proposal', 'proposal_number')
                    ->searchable()
                    ->preload(),
                \Filament\Forms\Components\TextInput::make('contract_number')
                    ->required()
                    ->unique(ignoreRecord: true),
                \Filament\Forms\Components\DatePicker::make('expiry_date'),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'terminated' => 'Terminated',
                    ])
                    ->required(),
                \Filament\Forms\Components\Select::make('reminder_status')
                    ->options([
                        '6_month' => '6 Months Before',
                        '3_month' => '3 Months Before',
                        '1_month' => '1 Month Before',
                    ]),
            ]);
    }
}
