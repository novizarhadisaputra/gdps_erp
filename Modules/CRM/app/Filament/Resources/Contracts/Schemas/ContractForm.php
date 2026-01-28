<?php

namespace Modules\CRM\Filament\Resources\Contracts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\ContractStatus;
use Modules\MasterData\Filament\Resources\Customers\Schemas\CustomerForm;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm(CustomerForm::schema()),
                Select::make('proposal_id')
                    ->relationship('proposal', 'proposal_number')
                    ->searchable()
                    ->preload(),
                TextInput::make('contract_number')
                    ->required()
                    ->unique(ignoreRecord: true),
                DatePicker::make('expiry_date'),
                ToggleButtons::make('status')
                    ->options(ContractStatus::class)
                    ->default(ContractStatus::Draft)
                    ->hiddenOn('create')
                    ->disabled()
                    ->inline()
                    ->required(),
                Select::make('reminder_status')
                    ->options([
                        '6_month' => '6 Months Before',
                        '3_month' => '3 Months Before',
                        '1_month' => '1 Month Before',
                    ]),
            ]);
    }
}
