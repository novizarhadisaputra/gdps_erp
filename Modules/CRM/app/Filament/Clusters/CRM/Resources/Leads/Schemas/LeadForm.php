<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\LeadStatus;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\WorkScheme;

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
                        ->relationship('customer', 'name')
                        ->label('Customer')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm(CustomerForm::schema())
                        ->createOptionUsing(fn (array $data) => Customer::create($data)->id),
                    Select::make('work_scheme_id')
                        ->relationship('workScheme', 'name')
                        ->label('Work Scheme')
                        ->required()
                        ->createOptionForm(WorkSchemeForm::schema())
                        ->createOptionUsing(fn (array $data) => WorkScheme::create($data)->id),
                    Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('Sales PIC')
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
                        ->disabled()
                        ->dehydrated(),
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

            // Documents section removed in favor of ManageProposals page
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::schema());
    }
}
