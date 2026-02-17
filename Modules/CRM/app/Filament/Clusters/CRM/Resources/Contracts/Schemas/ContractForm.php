<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Enums\ContractType;
use Modules\CRM\Enums\ReminderStatus;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Proposal;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerForm;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Contract Details')
                ->schema([
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->createOptionForm(CustomerForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(CustomerForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('proposal_id')
                        ->relationship('proposal', 'proposal_number')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->disabled(fn ($state) => filled($state))
                        ->dehydrated()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                return;
                            }
                            $proposal = Proposal::find($state);
                            if (! $proposal) {
                                return;
                            }
                            $set('customer_id', $proposal->customer_id);
                        }),
                    TextInput::make('contract_number')
                        ->required()
                        ->hiddenOn('create')
                        ->unique(Contract::class, 'contract_number', ignoreRecord: true),
                    Select::make('type')
                        ->options(ContractType::class)
                        ->default(ContractType::Agreement)
                        ->required()
                        ->native(false),
                    DatePicker::make('expiry_date'),
                    ToggleButtons::make('status')
                        ->options(ContractStatus::class)
                        ->default(ContractStatus::Draft)
                        ->hiddenOn('create')
                        ->disabled()
                        ->inline()
                        ->required(),
                    Select::make('reminder_status')
                        ->options(ReminderStatus::class),
                    SpatieMediaLibraryFileUpload::make('signed_contract')
                        ->collection('signed_contract')
                        ->label('Signed Contract Document')
                        ->disk('s3')
                        ->visibility('private')
                        ->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('termination_evidence')
                        ->collection('termination_evidence')
                        ->label('Termination Evidence')
                        ->disk('s3')
                        ->visibility('private')
                        ->visible(fn ($get) => $get('status') === ContractStatus::Terminated->value)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
}
