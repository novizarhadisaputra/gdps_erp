<?php

namespace Modules\MasterData\Filament\Resources\ApprovalRules;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Resources\ApprovalRules\Pages\CreateApprovalRule;
use Modules\MasterData\Filament\Resources\ApprovalRules\Pages\EditApprovalRule;
use Modules\MasterData\Filament\Resources\ApprovalRules\Pages\ListApprovalRules;
use Modules\MasterData\Models\ApprovalRule;

class ApprovalRuleResource extends Resource
{
    protected static ?string $model = ApprovalRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Rule Details')
                    ->schema([
                        \Filament\Forms\Components\Select::make('resource_type')
                            ->label('Resource Type')
                            ->options([
                                'Modules\Finance\Models\ProfitabilityAnalysis' => 'Profitability Analysis',
                                'Modules\Project\Models\Project' => 'Project',
                            ])
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('criteria_field')
                            ->label('Criteria Field')
                            ->placeholder('e.g. revenue_per_month')
                            ->required(),
                        \Filament\Forms\Components\Select::make('operator')
                            ->options([
                                '>' => 'Greater Than (>)',
                                '>=' => 'Greater Than or Equal (>=)',
                                '<' => 'Less Than (<)',
                                '<=' => 'Less Than or Equal (<=)',
                                '=' => 'Equal (=)',
                            ])
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('value')
                            ->numeric()
                            ->required(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Approval Config')
                    ->schema([
                        \Filament\Forms\Components\Select::make('approver_role')
                            ->label('Required Role')
                            ->options(fn () => \Spatie\Permission\Models\Role::pluck('name', 'name'))
                            ->required()
                            ->searchable(),
                        \Filament\Forms\Components\Select::make('signature_type')
                            ->options([
                                'Reviewer' => 'Reviewer',
                                'Approver' => 'Approver',
                                'Acknowledger' => 'Acknowledger',
                            ])
                            ->default('Approver')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('order')
                            ->numeric()
                            ->default(0),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('resource_type')
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->label('Resource'),
                \Filament\Tables\Columns\TextColumn::make('criteria_field'),
                \Filament\Tables\Columns\TextColumn::make('operator'),
                \Filament\Tables\Columns\TextColumn::make('value')
                    ->numeric(),
                \Filament\Tables\Columns\TextColumn::make('approver_role')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('signature_type'),
                \Filament\Tables\Columns\TextColumn::make('order')
                    ->sortable(),
                \Filament\Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApprovalRules::route('/'),
            'create' => CreateApprovalRule::route('/create'),
            'edit' => EditApprovalRule::route('/{record}/edit'),
        ];
    }
}
