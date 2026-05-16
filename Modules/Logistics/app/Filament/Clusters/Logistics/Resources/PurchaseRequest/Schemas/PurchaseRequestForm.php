<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\UnitOfMeasure;
use Modules\Project\Models\Project;

class PurchaseRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Purchase Request Details')
                    ->tabs([
                        Tab::make('General Information')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('pr_number')
                                                    ->label('Request Number')
                                                    ->placeholder('Auto-generated')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->prefixIcon(Heroicon::OutlinedHashtag)
                                                    ->helperText('This number will be generated automatically after saving.'),

                                                Select::make('requester_id')
                                                    ->label('Requested By')
                                                    ->options(Employee::all()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->required()
                                                    ->default(fn () => auth()->user()->employee_id ?? null)
                                                    ->prefixIcon(Heroicon::OutlinedUser)
                                                    ->placeholder('Select requester employee')
                                                    ->helperText('The employee who is initiating this request.'),

                                                Select::make('project_id')
                                                    ->label('Linked Project')
                                                    ->options(Project::all()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->required()
                                                    ->live()
                                                    ->prefixIcon(Heroicon::OutlinedBriefcase)
                                                    ->placeholder('Choose a project')
                                                    ->helperText('Select the project this request belongs to.'),

                                                TextInput::make('total_amount')
                                                    ->label('Total Estimated Cost')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->prefix('IDR')
                                                    ->prefixIcon(Heroicon::OutlinedBanknotes)
                                                    ->placeholder('0.00')
                                                    ->helperText('Sum of all item estimated prices.'),
                                            ]),

                                        Textarea::make('description')
                                            ->label('Justification / Purpose')
                                            ->rows(3)
                                            ->placeholder('Describe why these items are needed...')
                                            ->helperText('Provide a clear reason for this procurement request.')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Requested Items')
                            ->icon(Heroicon::OutlinedListBullet)
                            ->schema([
                                Section::make('Items List')
                                    ->description('List the items or services needed for this request.')
                                    ->schema([
                                        Repeater::make('items')
                                            ->relationship('items')
                                            ->schema([
                                                Grid::make(5)
                                                    ->schema([
                                                        Select::make('item_id')
                                                            ->label('Item')
                                                            ->options(Item::all()->pluck('name', 'id'))
                                                            ->searchable()
                                                            ->required()
                                                            ->live()
                                                            ->placeholder('Search item...')
                                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                                if (! $state) {
                                                                    return;
                                                                }

                                                                $item = Item::find($state);
                                                                if ($item) {
                                                                    $set('unit_of_measure_id', $item->unit_of_measure_id);
                                                                }
                                                            }),

                                                        TextInput::make('quantity')
                                                            ->label('Qty')
                                                            ->numeric()
                                                            ->required()
                                                            ->default(1)
                                                            ->live()
                                                            ->placeholder('1')
                                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateItemTotal($get, $set)),

                                                        Select::make('unit_of_measure_id')
                                                            ->label('UOM')
                                                            ->options(UnitOfMeasure::all()->pluck('name', 'id'))
                                                            ->disabled()
                                                            ->dehydrated()
                                                            ->required()
                                                            ->placeholder('Unit'),

                                                        TextInput::make('estimated_price')
                                                            ->label('Est. Unit Price')
                                                            ->numeric()
                                                            ->required()
                                                            ->prefix('IDR')
                                                            ->live()
                                                            ->placeholder('0.00')
                                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateItemTotal($get, $set)),

                                                        TextInput::make('total_estimated_price')
                                                            ->label('Subtotal')
                                                            ->numeric()
                                                            ->disabled()
                                                            ->dehydrated()
                                                            ->prefix('IDR')
                                                            ->placeholder('0.00'),
                                                    ]),
                                            ])
                                            ->columnSpanFull()
                                            ->itemLabel(fn (array $state): ?string => Item::find($state['item_id'] ?? null)?->name ?? 'New Request Item'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function updateItemTotal(Get $get, Set $set): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $price = (float) ($get('estimated_price') ?? 0);
        $set('total_estimated_price', $quantity * $price);
    }
}
