<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Logistics\Models\PurchaseRequest;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\Vendor;
use Modules\Project\Models\Project;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Purchase Order Details')
                    ->tabs([
                        Tab::make('Order Reference')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('po_number')
                                                    ->label('Order Number')
                                                    ->placeholder('Auto-generated')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->prefixIcon(Heroicon::OutlinedHashtag)
                                                    ->helperText('This number will be generated automatically after saving.'),

                                                Select::make('purchase_request_id')
                                                    ->label('Source Request (PR)')
                                                    ->options(PurchaseRequest::where('status', 'approved')->pluck('pr_number', 'id'))
                                                    ->searchable()
                                                    ->live()
                                                    ->prefixIcon(Heroicon::OutlinedDocumentText)
                                                    ->placeholder('Select an approved PR')
                                                    ->helperText('Automatically populate items by selecting an approved request.')
                                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                        if (! $state) {
                                                            return;
                                                        }

                                                        $pr = PurchaseRequest::with('items')->find($state);
                                                        if ($pr) {
                                                            $set('project_id', $pr->project_id);

                                                            $items = $pr->items->map(fn ($item) => [
                                                                'item_id' => $item->item_id,
                                                                'quantity' => $item->quantity,
                                                                'unit_price' => $item->estimated_price,
                                                                'tax_rate' => 11,
                                                                'total_price' => $item->total_estimated_price * 1.11,
                                                            ])->toArray();

                                                            $set('items', $items);
                                                        }
                                                    }),

                                                Select::make('vendor_id')
                                                    ->label('Vendor')
                                                    ->options(Vendor::all()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->required()
                                                    ->prefixIcon(Heroicon::OutlinedBuildingStorefront)
                                                    ->placeholder('Select vendor')
                                                    ->helperText('The supplier who will fulfill this order.'),

                                                Select::make('project_id')
                                                    ->label('Project')
                                                    ->options(Project::all()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->required()
                                                    ->prefixIcon(Heroicon::OutlinedBriefcase)
                                                    ->placeholder('Linked project')
                                                    ->helperText('The project this procurement is allocated to.'),

                                                Select::make('warehouse_id')
                                                    ->label('Target Warehouse')
                                                    ->options(\Modules\Logistics\Models\Warehouse::all()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->required()
                                                    ->prefixIcon(Heroicon::OutlinedHome)
                                                    ->placeholder('Select destination warehouse')
                                                    ->helperText('The warehouse where items will be stored upon completion.'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Items & Financials')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->schema([
                                Section::make('Financial Summary')
                                    ->description('Summary of costs including taxes and grand total.')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('total_amount')
                                                    ->label('Subtotal (Net)')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->prefix('IDR')
                                                    ->placeholder('0.00'),

                                                TextInput::make('tax_amount')
                                                    ->label('Tax (VAT 11%)')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->prefix('IDR')
                                                    ->placeholder('0.00'),

                                                TextInput::make('grand_total')
                                                    ->label('Grand Total')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->prefix('IDR')
                                                    ->placeholder('0.00'),
                                            ]),
                                    ]),

                                Section::make('Order Items')
                                    ->description('Specify quantities and agreed prices for each item.')
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
                                                            ->placeholder('Select item'),

                                                        TextInput::make('quantity')
                                                            ->label('Qty')
                                                            ->numeric()
                                                            ->required()
                                                            ->live()
                                                            ->placeholder('0')
                                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateItemTotal($get, $set)),

                                                        TextInput::make('unit_price')
                                                            ->label('Unit Price')
                                                            ->numeric()
                                                            ->required()
                                                            ->prefix('IDR')
                                                            ->live()
                                                            ->placeholder('0.00')
                                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateItemTotal($get, $set)),

                                                        TextInput::make('tax_rate')
                                                            ->label('Tax %')
                                                            ->numeric()
                                                            ->default(11)
                                                            ->live()
                                                            ->placeholder('11')
                                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateItemTotal($get, $set)),

                                                        TextInput::make('total_price')
                                                            ->label('Line Total')
                                                            ->numeric()
                                                            ->disabled()
                                                            ->dehydrated()
                                                            ->prefix('IDR')
                                                            ->placeholder('0.00'),
                                                    ]),
                                            ])
                                            ->columnSpanFull()
                                            ->itemLabel(fn (array $state): ?string => Item::find($state['item_id'] ?? null)?->name ?? 'New Order Item'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function updateItemTotal(Get $get, Set $set): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $price = (float) ($get('unit_price') ?? 0);
        $taxRate = (float) ($get('tax_rate') ?? 0);

        $subtotal = $quantity * $price;
        $tax = $subtotal * ($taxRate / 100);

        $set('total_price', $subtotal + $tax);
    }
}
