<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApprovalRules\Schemas;

use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\MinutesOfAgreement;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Enums\ApprovalSignatureType;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Modules\MasterData\Services\UnitService;
use Modules\Project\Models\Project;
use Spatie\Permission\Models\Role;

class ApprovalRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rule Details')
                    ->schema([
                        Select::make('resource_type')
                            ->label('Resource Type')
                            ->options([
                                ProfitabilityAnalysis::class => 'Profitability Analysis',
                                Project::class => 'Project',
                                Contract::class => 'Contract',
                                Proposal::class => 'Proposal',
                                MinutesOfAgreement::class => 'Minutes of Agreement',
                                GeneralInformation::class => 'General Information',
                            ])
                            ->required()
                            ->live(),

                        \Filament\Forms\Components\Repeater::make('conditions')
                            ->label('Conditions (ALL must be met - AND logic)')
                            ->schema([
                                Select::make('field')
                                    ->label('Criteria Field')
                                    ->options(function (Get $get) {
                                        $resourceType = $get('../../resource_type');

                                        return match ($resourceType) {
                                            ProfitabilityAnalysis::class => [
                                                'revenue_per_month' => 'Revenue',
                                                'margin_percentage' => 'Margin (%)',
                                                'net_profit' => 'Net Profit',
                                                'product_cluster_id' => 'Product Cluster',
                                            ],
                                            Contract::class, 'Modules\CRM\Models\Project', Project::class, Proposal::class => [
                                                'amount' => 'Amount / Value',
                                            ],
                                            MinutesOfAgreement::class => [
                                                'amount' => 'Amount / Value',
                                            ],
                                            GeneralInformation::class => [
                                                'sequence_number' => 'Sequence Number',
                                            ],
                                            default => [],
                                        };
                                    })
                                    ->required()
                                    ->live()
                                    ->columnSpan(2),
                                Select::make('operator')
                                    ->options([
                                        '>' => 'Greater Than (>)',
                                        '>=' => 'Greater Than or Equal (>=)',
                                        '<' => 'Less Than (<)',
                                        '<=' => 'Less Than or Equal (<=)',
                                        '=' => 'Equal (=)',
                                        'in' => 'In (Comma Separated)',
                                        'between' => 'Between',
                                    ])
                                    ->live()
                                    ->required()
                                    ->columnSpan(2),
                                Hidden::make('value'),

                                TextInput::make('value_text')
                                    ->label('Value')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix(fn (Get $get) => in_array($get('field'), ['revenue_per_month', 'net_profit', 'amount']) ? 'IDR' : null)
                                    ->suffix(fn (Get $get) => $get('field') === 'margin_percentage' ? '%' : null)
                                    ->required(fn (Get $get) => empty($get('value_select')) && ! array_key_exists($get('field'), static::getRelationshipFields()))
                                    ->live()
                                    ->afterStateUpdated(fn ($state, Set $set) => $set('value', $state))
                                    ->afterStateHydrated(function ($component, Set $set, Get $get) {
                                        $field = $get('field');
                                        if ($field && ! array_key_exists($field, static::getRelationshipFields())) {
                                            $set('value_text', $get('value'));
                                        }
                                    })
                                    ->visible(function (Get $get) {
                                        $field = $get('field');
                                        if (! $field) {
                                            return true;
                                        }

                                        return ! array_key_exists($field, static::getRelationshipFields());
                                    })
                                    ->columnSpan(2),

                                Select::make('value_select')
                                    ->label('Value')
                                    ->options(function (Get $get) {
                                        $field = $get('field');
                                        $mapping = static::getRelationshipFields()[$field] ?? null;

                                        if (! $mapping) {
                                            return [];
                                        }

                                        $model = $mapping['model'];
                                        $label = $mapping['label'];

                                        return $model::query()->pluck($label, 'id');
                                    })
                                    ->searchable()
                                    ->multiple(fn (Get $get) => $get('operator') === 'in')
                                    ->required(fn (Get $get) => empty($get('value_text')) && array_key_exists($get('field'), static::getRelationshipFields()))
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($get('operator') === 'in') {
                                            $set('value', (array) $state);
                                        } else {
                                            $set('value', (string) $state);
                                        }
                                    })
                                    ->afterStateHydrated(function ($component, Set $set, Get $get) {
                                        $field = $get('field');
                                        if ($field && array_key_exists($field, static::getRelationshipFields())) {
                                            $value = $get('value');
                                            // Ensure array if operator is 'in'
                                            if ($get('operator') === 'in' && is_string($value)) {
                                                $value = explode(',', $value);
                                            }
                                            $set('value_select', $value);
                                        }
                                    })
                                    ->visible(function (Get $get) {
                                        $field = $get('field');

                                        return $field && array_key_exists($field, static::getRelationshipFields());
                                    })
                                    ->columnSpan(2),

                                TextInput::make('max_value')
                                    ->label('To (Max Value)')
                                    ->numeric()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix(fn (Get $get) => in_array($get('field'), ['revenue_per_month', 'net_profit', 'amount']) ? 'IDR' : null)
                                    ->suffix(fn (Get $get) => $get('field') === 'margin_percentage' ? '%' : null)
                                    ->required(fn (Get $get) => $get('operator') === 'between')
                                    ->visible(fn (Get $get) => $get('operator') === 'between')
                                    ->columnSpan(2),
                            ])
                            ->columns(8)
                            ->columnSpanFull()
                            ->defaultItems(1),
                    ])->columns(2)->columnSpanFull(),

                Section::make('Approval Config')
                    ->schema([
                        Select::make('approver_type')
                            ->options([
                                'Role' => 'Role',
                                'User' => 'User',
                                'Unit' => 'Unit',
                                'Position' => 'Position',
                            ])
                            ->disabled()
                            ->dehydrated()
                            ->default('Role'),

                        Select::make('approver_role')
                            ->label('Role(s)')
                            ->options(fn () => Role::pluck('name', 'id'))
                            ->searchable()
                            ->multiple()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! empty($state)) {
                                    $set('approver_type', 'Role');
                                    $set('approver_user_id', null);
                                    $set('approver_unit_id', null);
                                    $set('approver_position', null);
                                }
                            }),

                        Select::make('approver_user_id')
                            ->label('User(s)')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->multiple()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! empty($state)) {
                                    $set('approver_type', 'User');
                                    $set('approver_role', null);
                                    $set('approver_unit_id', null);
                                    $set('approver_position', null);
                                }
                            }),

                        Select::make('approver_unit_id')
                            ->label('Unit(s)')
                            ->options(fn () => app(UnitService::class)->getAllUnits()->pluck('name', 'id'))
                            ->searchable()
                            ->multiple()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! empty($state)) {
                                    $set('approver_type', 'Unit');
                                    $set('approver_role', null);
                                    $set('approver_user_id', null);
                                    $set('approver_position', null);
                                }
                            }),

                        Select::make('approver_position')
                            ->label('Job Position(s)')
                            ->options(User::distinct()->whereNotNull('position')->pluck('position', 'position'))
                            ->searchable()
                            ->multiple()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! empty($state)) {
                                    $set('approver_type', 'Position');
                                    $set('approver_role', null);
                                    $set('approver_user_id', null);
                                    $set('approver_unit_id', null);
                                }
                            }),
                        Select::make('signature_type')
                            ->options(ApprovalSignatureType::class)
                            ->default(ApprovalSignatureType::Approver)
                            ->required(),
                        TextInput::make('order')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }

    protected static function getRelationshipFields(): array
    {
        return [
            'product_cluster_id' => [
                'model' => ProductCluster::class,
                'label' => 'name',
            ],
            'project_area_id' => [
                'model' => ProjectArea::class,
                'label' => 'name',
            ],
            'work_scheme_id' => [
                'model' => WorkScheme::class,
                'label' => 'name',
            ],
            'tax_id' => [
                'model' => Tax::class,
                'label' => 'name',
            ],
            'customer_id' => [
                'model' => \Modules\CRM\Models\Customer::class,
                'label' => 'name',
            ],
            'lead_id' => [
                'model' => Lead::class,
                'label' => 'document_number',
            ],
        ];
    }
}
