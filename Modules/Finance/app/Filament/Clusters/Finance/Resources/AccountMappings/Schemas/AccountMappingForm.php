<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;
use Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\Schemas\ChartOfAccountForm;
use Modules\Finance\Models\ChartOfAccount;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\RevenueType;

class AccountMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mapping Target')
                    ->description('Select the entity this mapping applies to.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->placeholder('Select a customer')
                                    ->helperText('The main client entity this account mapping belongs to.')
                                    ->options(Customer::pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record && $record->mappable_type === Customer::class) {
                                            $component->state($record->mappable_id);
                                        } elseif ($record && $record->mappable_type === ProjectArea::class && $record->mappable) {
                                            $component->state($record->mappable->getCustomer()?->id);
                                        }
                                    })
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $set('project_area_id', null);
                                        $set('mappable_id', $state);
                                        $set('mappable_type', Customer::class);
                                    }),

                                Select::make('project_area_id')
                                    ->label('Project Area (Optional)')
                                    ->placeholder('Select a project area')
                                    ->helperText('Specify a sub-area for more granular mapping. Leave empty to apply at Customer level.')
                                    ->options(function (Get $get) {
                                        $customerId = $get('customer_id');
                                        if (! $customerId) {
                                            return [];
                                        }
                                        $customer = Customer::find($customerId);
                                        if (! $customer) {
                                            return [];
                                        }

                                        return ProjectArea::getAllDescendantsFor($customer)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->live()
                                    ->createOptionForm(ProjectAreaForm::schema(includeParent: false))
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->createOptionUsing(function (array $data, Get $get) {
                                        $customerId = $get('customer_id');
                                        if (! $customerId) {
                                            Notification::make()
                                                ->title('Customer required')
                                                ->body('Please select a customer before creating a project area.')
                                                ->danger()
                                                ->send();

                                            return null;
                                        }

                                        $area = ProjectArea::create($data);
                                        $area->customers()->attach($customerId);

                                        return $area->id;
                                    })
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record && $record->mappable_type === ProjectArea::class) {
                                            $component->state($record->mappable_id);
                                        }
                                    })
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        if ($state) {
                                            $set('mappable_id', $state);
                                            $set('mappable_type', ProjectArea::class);
                                        } else {
                                            $set('mappable_id', $get('customer_id'));
                                            $set('mappable_type', Customer::class);
                                        }
                                    }),
                            ]),

                        Hidden::make('mappable_id')->required(),
                        Hidden::make('mappable_type')->required(),
                    ]),

                Section::make('Mapping Configuration')
                    ->description('Define the GL account for specific revenue types and segments.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Mapping Type')
                                    ->placeholder('Select mapping category')
                                    ->helperText('Identify if this account is used for Accruals, Revenue recognition, or Receivables.')
                                    ->options([
                                        'accrual' => 'Accrued Revenue',
                                        'revenue' => 'Revenue',
                                        'receivable' => 'Account Receivable (AR)',
                                    ])
                                    ->required(),

                                Select::make('chart_of_account_id')
                                    ->label('Chart of Account (GL)')
                                    ->placeholder('Search by code or name')
                                    ->helperText('The General Ledger account from the SAP-aligned chart of accounts.')
                                    ->options(function () {
                                        return ChartOfAccount::query()
                                            ->get()
                                            ->mapWithKeys(fn ($item) => [$item->id => "{$item->code} - {$item->name}"]);
                                    })
                                    ->getSearchResultsUsing(function (string $search) {
                                        return ChartOfAccount::query()
                                            ->where('name', 'ILIKE', "%{$search}%")
                                            ->orWhere('code', 'ILIKE', "%{$search}%")
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(fn ($item) => [$item->id => "{$item->code} - {$item->name}"]);
                                    })
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm(ChartOfAccountForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver()),

                                Select::make('revenue_type_id')
                                    ->label('Revenue Type')
                                    ->placeholder('All Revenue Types')
                                    ->helperText('Optional: apply this mapping only to a specific revenue category.')
                                    ->options(RevenueType::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),

                                Select::make('revenue_segment_id')
                                    ->label('Revenue Segment')
                                    ->placeholder('All Revenue Segments')
                                    ->helperText('Optional: apply this mapping only to a specific business segment.')
                                    ->options(RevenueSegment::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),

                                TextInput::make('note')
                                    ->label('Reference Note')
                                    ->placeholder('e.g., Original reference from mapping spreadsheet')
                                    ->helperText('Internal notes for auditing and reconciliation purposes.')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
