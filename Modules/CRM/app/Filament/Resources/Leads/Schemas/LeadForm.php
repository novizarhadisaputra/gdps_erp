<?php

namespace Modules\CRM\Filament\Resources\Leads\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\LeadStatus;
use Modules\MasterData\Filament\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\WorkScheme;
use App\Models\User;


class LeadForm
{
    public static function schema(): array
    {
        return [
            Section::make('Lead Details')
                ->description('Basic information about the sales lead.')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Select::make('customer_id')
                        ->label('Customer')
                        ->options(Customer::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm(CustomerForm::schema())
                        ->createOptionUsing(fn (array $data) => Customer::create($data)->id),
                    Select::make('work_scheme_id')
                        ->label('Work Scheme')
                        ->options(WorkScheme::pluck('name', 'id'))
                        ->required()
                        ->createOptionForm(WorkSchemeForm::schema())
                        ->createOptionUsing(fn (array $data) => WorkScheme::create($data)->id),
                    Select::make('user_id')
                        ->label('Sales PIC')
                        ->options(User::pluck('name', 'id'))
                        ->default(auth()->id())
                        ->disabled(fn () => ! auth()->user()->hasRole('super_admin'))
                        ->dehydrated()
                        ->required()
                        ->searchable()
                        ->preload(),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->columns(2),

            Section::make('Pipeline & Forecast')
                ->description('Status and financial projections.')
                ->schema([
                    Select::make('status')
                        ->options(LeadStatus::class)
                        ->required()
                        ->default(LeadStatus::Lead)
                        ->native(false)
                        ->hiddenOn('create'),
                    TextInput::make('estimated_amount')
                        ->numeric()
                        ->prefix('IDR')
                        ->maxValue(42949672.95),
                    TextInput::make('probability')
                        ->numeric()
                        ->suffix('%')
                        ->minValue(0)
                        ->maxValue(100),
                    DatePicker::make('expected_closing_date')
                        ->native(false),
                ])
                ->columnSpanFull()
                ->columns(2),

            Section::make('Documents')
                ->schema([
                    \Filament\Forms\Components\Repeater::make('proposals')
                        ->relationship()
                        ->schema([
                            TextInput::make('proposal_number')
                                ->required()
                                ->unique(\Modules\CRM\Models\Proposal::class, 'proposal_number', ignoreRecord: true),
                            TextInput::make('amount')
                                ->numeric()
                                ->prefix('IDR')
                                ->required(),
                            Select::make('status')
                                ->options(\Modules\CRM\Enums\ProposalStatus::class)
                                ->default(\Modules\CRM\Enums\ProposalStatus::Draft)
                                ->required(),
                            DatePicker::make('submission_date'),
                        ])
                        ->columns(2)
                        ->addActionLabel('Add Proposal'),
                ])
                ->columnSpanFull(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::schema());
    }
}
