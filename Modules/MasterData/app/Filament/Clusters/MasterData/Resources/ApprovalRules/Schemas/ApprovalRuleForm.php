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
use Modules\CRM\Models\Proposal;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Enums\ApprovalSignatureType;
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
                                GeneralInformation::class => 'General Information',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state === GeneralInformation::class) {
                                    $set('criteria_field', 'sequence_number');
                                    $set('operator', '>');
                                    $set('value', -1);
                                }
                            }),
                        Select::make('criteria_field')
                            ->label('Criteria Field')
                            ->options(fn (Get $get): array => match ($get('resource_type')) {
                                ProfitabilityAnalysis::class => [
                                    'revenue_per_month' => 'Revenue',
                                    'margin_percentage' => 'Margin (%)',
                                    'net_profit' => 'Net Profit',
                                ],
                                Contract::class, 'Modules\CRM\Models\Project', Project::class, Proposal::class => [
                                    'amount' => 'Amount / Value',
                                ],
                                GeneralInformation::class => [
                                    'sequence_number' => 'Sequence Number',
                                ],
                                default => [],
                            })
                            ->required(fn (Get $get) => $get('resource_type') !== GeneralInformation::class)
                            ->visible(fn (Get $get) => $get('resource_type') !== GeneralInformation::class),
                        Select::make('operator')
                            ->options([
                                '>' => 'Greater Than (>)',
                                '>=' => 'Greater Than or Equal (>=)',
                                '<' => 'Less Than (<)',
                                '<=' => 'Less Than or Equal (<=)',
                                '=' => 'Equal (=)',
                            ])
                            ->required(fn (Get $get) => $get('resource_type') !== GeneralInformation::class)
                            ->visible(fn (Get $get) => $get('resource_type') !== GeneralInformation::class),
                        TextInput::make('value')
                            ->numeric()
                            ->prefix(fn (Get $get) => in_array($get('criteria_field'), ['revenue_per_month', 'net_profit', 'amount']) ? 'IDR' : null)
                            ->suffix(fn (Get $get) => $get('criteria_field') === 'margin_percentage' ? '%' : null)
                            ->required(fn (Get $get) => $get('resource_type') !== GeneralInformation::class)
                            ->visible(fn (Get $get) => $get('resource_type') !== GeneralInformation::class),
                    ])->columns(2)->columnSpanFull(),

                Section::make('Approval Config')
                    ->schema([
                        Hidden::make('approver_type')
                            ->default('Role'),

                        Select::make('approver_role')
                            ->label('Role(s)')
                            ->options(fn () => Role::pluck('name', 'name'))
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
                            ->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Set $set) {
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
}
