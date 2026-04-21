<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\SalesOrderAmendmentStatus;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\UnitOfMeasure;

class AmendmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Documents')
                    ->description('Download the draft amendment for signing, then upload the scanned signed document to process approval.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('draft_soa')
                                    ->label('Draft SOA (Unsigned)')
                                    ->collection('draft_soa')
                                    ->disk('s3')
                                    ->downloadable()
                                    ->openable()
                                    ->helperText('System-generated draft document that is not yet signed.'),

                                SpatieMediaLibraryFileUpload::make('signed_soa')
                                    ->label('Signed SOA (Final Scan)')
                                    ->collection('signed_soa')
                                    ->disk('s3')
                                    ->downloadable()
                                    ->openable()
                                    ->helperText('Upload the scanned document that has been signed by both parties.')
                                    ->required(fn ($get) => $get('status') === SalesOrderAmendmentStatus::Submitted->value),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Amendment Metadata')
                    ->description('Basic information about this amendment. This data is historical and immutable.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amendment_number')
                                    ->label('Amendment Number')
                                    ->helperText('Sequence number of the contract change.')
                                    ->disabled(),
                                DatePicker::make('amendment_date')
                                    ->label('Amendment Date')
                                    ->helperText('Approved date of the PA revision.')
                                    ->disabled(),
                                Select::make('status')
                                    ->label('Status')
                                    ->options(SalesOrderAmendmentStatus::class)
                                    ->disabled(),
                            ]),
                        Textarea::make('reason')
                            ->label('Reason for Change')
                            ->placeholder('Describe why this amendment is being made...')
                            ->helperText('This justification will appear in the amendment document.')
                            ->disabled(fn ($record) => $record?->status !== SalesOrderAmendmentStatus::Draft)
                            ->required(),
                    ])->columnSpanFull(),

                Section::make('Comparison: Manpower & Pricing')
                    ->description('Detailed comparison between original contract data (Before) and revised contract data (After). Use this to verify changes in value or personnel count.')
                    ->schema([

                        Section::make('Before (Original)')
                            ->description('A combined snapshot of the active Sales Order or previous amendment data.')
                            ->schema([
                                TextEntry::make('original_snapshot_combined')
                                    ->label('Original Contract Snapshot')
                                    ->view('crm::filament.components.combined-snapshot-table', fn ($record) => [
                                        'items' => $record?->before_snapshot['items'] ?? [],
                                        'manpower' => $record?->before_snapshot['manpower_details'] ?? [],
                                    ]),
                            ]),

                        Section::make('After (Revised)')
                            ->description('Enter the new/revised data for this amendment. This will update the parent Sales Order once approved.')
                            ->schema([
                                Repeater::make('after_snapshot_unified')
                                    ->label('Amendment Entries (Revised Data)')
                                    ->schema([
                                        Grid::make(3) // 3 components per row for maximum clarity
                                            ->schema([
                                                // Row 1: Primary Identification
                                                Select::make('type')
                                                    ->label('Category')
                                                    ->options([
                                                        'item' => 'Service Item',
                                                        'personnel' => 'Personnel',
                                                    ])
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(fn ($set) => $set('description', null))
                                                    ->columnSpan(1),

                                                Select::make('description')
                                                    ->label(fn ($get) => $get('type') === 'personnel' ? 'Job Position' : 'Item Name')
                                                    ->placeholder('Select item or position...')
                                                    ->searchable()
                                                    ->preload()
                                                    ->options(function ($get) {
                                                        $type = $get('type');
                                                        if ($type === 'personnel') {
                                                            return JobPosition::query()->where('is_active', true)->pluck('name', 'name');
                                                        }
                                                        return Item::query()->where('is_active', true)->pluck('name', 'name');
                                                    })
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, $get, $set) {
                                                        if (! $state) {
                                                            return;
                                                        }

                                                        $type = $get('type');
                                                        if ($type === 'personnel') {
                                                            $position = JobPosition::where('name', $state)->first();
                                                            if ($position) {
                                                                $set('uom', 'Person');
                                                                // If there's a default price for position, we could set it here
                                                                // For now, Manpower usually has custom pricing but position name is fixed
                                                            }
                                                        } else {
                                                            $item = Item::where('name', $state)->first();
                                                            if ($item) {
                                                                $uomName = UnitOfMeasure::find($item->unit_of_measure_id)?->name;
                                                                if ($uomName) {
                                                                    $set('uom', $uomName);
                                                                }
                                                                $set('unit_price', $item->price);
                                                                
                                                                $qty = floatval($get('quantity') ?? 0);
                                                                $set('total_price', $qty * floatval($item->price));
                                                            }
                                                        }
                                                    })
                                                    ->columnSpan(1),

                                                Select::make('uom')
                                                    ->label('Unit')
                                                    ->options(UnitOfMeasure::pluck('name', 'name')->toArray())
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->live()
                                                    ->columnSpan(1),

                                                // Row 2: Comparison & Revision
                                                TextEntry::make('old_qty')
                                                    ->label('Qty (Original)')
                                                    ->state(function ($get, $record) {
                                                        $type = $get('type');
                                                        $name = $get('description');
                                                        if (! $name) {
                                                            return 0;
                                                        }
                                                        $snapshotKey = $type === 'personnel' ? 'manpower_details' : 'items';
                                                        $nameKey = $type === 'personnel' ? 'job_position_name' : 'description';

                                                        return collect($record?->before_snapshot[$snapshotKey] ?? [])->firstWhere($nameKey, $name)['quantity'] ?? 0;
                                                    })
                                                    ->columnSpan(1),

                                                TextEntry::make('qty_change')
                                                    ->label('Net Change')
                                                    ->state(function ($get, $record) {
                                                        $type = $get('type');
                                                        $name = $get('description');
                                                        $new = $get('quantity') ?? 0;
                                                        $snapshotKey = $type === 'personnel' ? 'manpower_details' : 'items';
                                                        $nameKey = $type === 'personnel' ? 'job_position_name' : 'description';
                                                        $old = collect($record?->before_snapshot[$snapshotKey] ?? [])->firstWhere($nameKey, $name)['quantity'] ?? 0;
                                                        $delta = $new - $old;
                                                        $sign = $delta > 0 ? '+' : '';

                                                        return $sign.$delta;
                                                    })
                                                    ->badge()
                                                    ->color(fn ($state) => str_contains($state, '+') ? 'success' : (str_contains($state, '-') ? 'danger' : 'gray'))
                                                    ->columnSpan(1),

                                                TextInput::make('quantity')
                                                    ->label('Qty (Revised)')
                                                    ->numeric()
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, $get, $set) {
                                                        $qty = floatval($state ?? 0);
                                                        $unitPrice = floatval($get('unit_price') ?? 0);
                                                        $set('total_price', $qty * $unitPrice);
                                                    })
                                                    ->columnSpan(1),

                                                // Row 3: Financials & Notes
                                                TextInput::make('unit_price')
                                                    ->label(fn ($get) => ($get('type') ?? 'item') === 'personnel' ? 'Rate / Person (IDR)' : 'Unit Price (IDR)')
                                                    ->numeric()
                                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                    ->prefix('IDR ')
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, $get, $set) {
                                                        $qty = floatval($get('quantity') ?? 0);
                                                        // Use a helper to parse numeric string if needed, but numeric() + currencyMask handles it
                                                        $unitPrice = floatval($state ?? 0);
                                                        $set('total_price', $qty * $unitPrice);
                                                    })
                                                    ->columnSpan(1),

                                                TextInput::make('total_price')
                                                    ->label(fn ($get) => ($get('type') ?? 'item') === 'personnel' ? 'Total Monthly Cost (IDR)' : 'Total Price / Month (IDR)')
                                                    ->numeric()
                                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                    ->prefix('IDR ')
                                                    ->required()
                                                    ->readonly()
                                                    ->live()
                                                    ->extraInputAttributes(['class' => 'font-bold text-primary-600'])
                                                    ->columnSpan(1),

                                                TextInput::make('note')
                                                    ->label('Notes')
                                                    ->placeholder('Reason for change...')
                                                    ->columnSpan(1),
                                            ]),
                                    ])

                                    ->reorderable()
                                    ->addActionLabel('Add New Record (Item/Personnel)')
                                    ->itemLabel(fn ($state) => ($state['type'] ?? 'New') === 'personnel' ? 'Personnel: '.($state['description'] ?? '...') : 'Item: '.($state['description'] ?? '...')),

                                TextEntry::make('amendment_summary_live')
                                    ->label('')
                                    ->view('crm::filament.components.amendment-summary-table', fn ($get, $record) => [
                                        'before' => $record?->before_snapshot ?? [],
                                        'after' => $get('after_snapshot_unified') ?? [],
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
