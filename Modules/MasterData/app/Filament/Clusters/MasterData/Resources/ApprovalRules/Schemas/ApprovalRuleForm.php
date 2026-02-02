<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApprovalRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
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
                                'Modules\Finance\Models\ProfitabilityAnalysis' => 'Profitability Analysis',
                                'Modules\Project\Models\Project' => 'Project',
                                'Modules\CRM\Models\Contract' => 'Contract',
                                'Modules\CRM\Models\Proposal' => 'Proposal',
                                'Modules\CRM\Models\GeneralInformation' => 'General Information',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Set $set) {
                                if ($state === 'Modules\CRM\Models\GeneralInformation') {
                                    $set('criteria_field', 'sequence_number');
                                    $set('operator', '>');
                                    $set('value', -1);
                                }
                            }),
                        Select::make('criteria_field')
                            ->label('Criteria Field')
                            ->options(fn (Get $get): array => match ($get('resource_type')) {
                                'Modules\Finance\Models\ProfitabilityAnalysis' => [
                                    'revenue_per_month' => 'Revenue',
                                    'margin_percentage' => 'Margin (%)',
                                    'net_profit' => 'Net Profit',
                                ],
                                'Modules\CRM\Models\Contract', 'Modules\CRM\Models\Project', 'Modules\Project\Models\Project', 'Modules\CRM\Models\Proposal' => [
                                    'amount' => 'Amount / Value',
                                ],
                                'Modules\CRM\Models\GeneralInformation' => [
                                    'sequence_number' => 'Sequence Number',
                                ],
                                default => [],
                            })
                            ->required(fn (Get $get) => $get('resource_type') !== 'Modules\CRM\Models\GeneralInformation')
                            ->visible(fn (Get $get) => $get('resource_type') !== 'Modules\CRM\Models\GeneralInformation'),
                        Select::make('operator')
                            ->options([
                                '>' => 'Greater Than (>)',
                                '>=' => 'Greater Than or Equal (>=)',
                                '<' => 'Less Than (<)',
                                '<=' => 'Less Than or Equal (<=)',
                                '=' => 'Equal (=)',
                            ])
                            ->required(fn (Get $get) => $get('resource_type') !== 'Modules\CRM\Models\GeneralInformation')
                            ->visible(fn (Get $get) => $get('resource_type') !== 'Modules\CRM\Models\GeneralInformation'),
                        TextInput::make('value')
                            ->numeric()
                            ->prefix(fn (Get $get) => in_array($get('criteria_field'), ['revenue_per_month', 'net_profit', 'amount']) ? 'IDR' : null)
                            ->suffix(fn (Get $get) => $get('criteria_field') === 'margin_percentage' ? '%' : null)
                            ->required(fn (Get $get) => $get('resource_type') !== 'Modules\CRM\Models\GeneralInformation')
                            ->visible(fn (Get $get) => $get('resource_type') !== 'Modules\CRM\Models\GeneralInformation'),
                    ])->columns(2)->columnSpanFull(),

                Section::make('Approval Config')
                    ->schema([
                        Select::make('approver_type')
                            ->label('Approver Based On')
                            ->options([
                                'Role' => 'Role (Spatie)',
                                'User' => 'Specific User',
                                'Unit' => 'Unit / Organization',
                                'Position' => 'Job Position',
                            ])
                            ->default('Role')
                            ->live()
                            ->afterStateUpdated(fn (\Filament\Schemas\Components\Utilities\Set $set) => $set('approver_role', null) & $set('approver_user_id', null) & $set('approver_unit_id', null) & $set('approver_position', null))
                            ->required(),

                        Select::make('approver_role')
                            ->label('Role(s)')
                            ->options(fn () => Role::pluck('name', 'name'))
                            ->searchable()
                            ->multiple()
                            ->visible(fn (Get $get) => $get('approver_type') === 'Role')
                            ->required(fn (Get $get) => $get('approver_type') === 'Role'),

                        Select::make('approver_user_id')
                            ->label('User(s)')
                            ->options(\App\Models\User::pluck('name', 'id'))
                            ->searchable()
                            ->multiple()
                            ->visible(fn (Get $get) => $get('approver_type') === 'User')
                            ->required(fn (Get $get) => $get('approver_type') === 'User'),

                        Select::make('approver_unit_id')
                            ->label('Unit(s)')
                            ->options(fn () => app(\Modules\MasterData\Services\UnitService::class)->getAllUnits()->pluck('name', 'id'))
                            ->searchable()
                            ->multiple()
                            ->visible(fn (Get $get) => $get('approver_type') === 'Unit')
                            ->required(fn (Get $get) => $get('approver_type') === 'Unit'),

                        Select::make('approver_position')
                            ->label('Job Position(s)')
                            ->options(\App\Models\User::distinct()->whereNotNull('position')->pluck('position', 'position'))
                            ->searchable()
                            ->multiple()
                            ->visible(fn (Get $get) => $get('approver_type') === 'Position')
                            ->required(fn (Get $get) => $get('approver_type') === 'Position'),
                        Select::make('signature_type')
                            ->options([
                                'Reviewer' => 'Reviewer',
                                'Approver' => 'Approver',
                                'Acknowledger' => 'Acknowledger',
                            ])
                            ->default('Approver')
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
