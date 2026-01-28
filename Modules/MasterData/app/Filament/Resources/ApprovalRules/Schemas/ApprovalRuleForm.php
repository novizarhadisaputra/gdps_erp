<?php

namespace Modules\MasterData\Filament\Resources\ApprovalRules\Schemas;

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
                            ])
                            ->required()
                            ->live(),
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
                                default => [],
                            })
                            ->required(),
                        Select::make('operator')
                            ->options([
                                '>' => 'Greater Than (>)',
                                '>=' => 'Greater Than or Equal (>=)',
                                '<' => 'Less Than (<)',
                                '<=' => 'Less Than or Equal (<=)',
                                '=' => 'Equal (=)',
                            ])
                            ->required(),
                        TextInput::make('value')
                            ->numeric()
                            ->required(),
                    ])->columns(2)->columnSpanFull(),

                Section::make('Approval Config')
                    ->schema([
                        Select::make('approver_role')
                            ->label('Required Role')
                            ->options(fn () => Role::pluck('name', 'name'))
                            ->required()
                            ->searchable(),
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
