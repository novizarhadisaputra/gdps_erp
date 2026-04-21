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
                    ->description('Unduh draft amandemen untuk ditandatangani, lalu unggah kembali hasil pindaian (Scan) dokumen yang telah ditandatangani untuk memproses persetujuan.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('draft_soa')
                                    ->label('Draft SOA (Unsigned)')
                                    ->collection('draft_soa')
                                    ->disk('s3')
                                    ->downloadable()
                                    ->openable()
                                    ->helperText('Dokumen draf hasil sistem yang belum ditandatangani.'),

                                SpatieMediaLibraryFileUpload::make('signed_soa')
                                    ->label('Signed SOA (Final Scan)')
                                    ->collection('signed_soa')
                                    ->disk('s3')
                                    ->downloadable()
                                    ->openable()
                                    ->helperText('Unggah pindaian dokumen yang telah ditandatangani oleh kedua belah pihak.')
                                    ->required(fn ($get) => $get('status') === SalesOrderAmendmentStatus::Submitted->value),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Amendment Metadata')
                    ->description('Informasi dasar mengenai amandemen ini. Data ini bersifat historis dan tidak dapat diubah.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amendment_number')
                                    ->label('No. Amandemen')
                                    ->helperText('Nomor urut perubahan kontrak.')
                                    ->disabled(),
                                DatePicker::make('amendment_date')
                                    ->label('Tanggal Amandemen')
                                    ->helperText('Tanggal disetujuinya revisi PA.')
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
                    ->description('Perbandingan rinci antara data kontrak asli (Before) dan data kontrak baru hasil revisi (After). Gunakan ini untuk memverifikasi perubahan nilai atau jumlah personil.')
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
                                                Select::make('description')
                                                    ->label('Description / Position')
                                                    ->options(function () {
                                                        $items = Item::pluck('name', 'name')->toArray();
                                                        $positions = JobPosition::pluck('name', 'name')->toArray();
                                                        return array_merge(['' => 'Select Item/Position...'], $items, $positions);
                                                    })
                                                    ->searchable()
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, $set) {
                                                        if (!$state) return;
                                                        
                                                        // Detective logic: Is this a Job Position?
                                                        $position = JobPosition::where('name', $state)->first();
                                                        if ($position) {
                                                            $set('type', 'personnel');
                                                            $set('uom', 'Person');
                                                        } else {
                                                            $set('type', 'item');
                                                            $item = Item::where('name', $state)->first();
                                                            if ($item) {
                                                                $uomName = UnitOfMeasure::find($item->unit_of_measure_id)?->name;
                                                                if ($uomName) $set('uom', $uomName);
                                                            }
                                                        }
                                                    })
                                                    ->columnSpan(1),

                                                Select::make('type')
                                                    ->label('Category')
                                                    ->options([
                                                        'item' => 'Service Item',
                                                        'personnel' => 'Personnel',
                                                    ])
                                                    ->required()
                                                    ->live()
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
                                                        if (! $name) return 0;
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
                                                        $delta = $new - $old; $sign = $delta > 0 ? '+' : '';
                                                        return $sign . $delta;
                                                    })
                                                    ->badge()
                                                    ->color(fn ($state) => str_contains($state, '+') ? 'success' : (str_contains($state, '-') ? 'danger' : 'gray'))
                                                    ->columnSpan(1),

                                                TextInput::make('quantity')
                                                    ->label('Qty (Revised)')
                                                    ->numeric()
                                                    ->required()
                                                    ->live()
                                                    ->columnSpan(1),

                                                // Row 3: Financials & Notes
                                                TextInput::make('total_price')
                                                    ->label('Price / Month (IDR)')
                                                    ->numeric()
                                                    ->prefix('IDR')
                                                    ->required()
                                                    ->visible(fn ($get) => $get('type') === 'item')
                                                    ->columnSpan(1),

                                                TextInput::make('note')
                                                    ->label('Notes')
                                                    ->placeholder('Reason for change...')
                                                    ->columnSpan(fn ($get) => $get('type') === 'item' ? 2 : 3),
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
