<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;
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
                                    ->helperText('Leave empty to apply mapping at the Customer level.')
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
                                    ->options([
                                        'accrual' => 'Accrual',
                                        'revenue' => 'Revenue',
                                        'receivable' => 'Receivable',
                                        'unbilled_receivable' => 'Unbilled Receivable',
                                        'expense' => 'Expense',
                                    ])
                                    ->required(),

                                Select::make('chart_of_account_id')
                                    ->label('Chart of Account (GL)')
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
                                    ->required(),

                                Select::make('revenue_type_id')
                                    ->label('Revenue Type')
                                    ->options(RevenueType::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),

                                Select::make('revenue_segment_id')
                                    ->label('Revenue Segment')
                                    ->options(RevenueSegment::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }
}
