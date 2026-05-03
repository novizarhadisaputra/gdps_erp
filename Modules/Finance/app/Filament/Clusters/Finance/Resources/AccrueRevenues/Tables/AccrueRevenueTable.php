<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Exports\SapAccrualExport;
use Modules\Finance\Models\AccountMapping;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Services\AccrualMappingService;

class AccrueRevenueTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (AccrueRevenueStatus $state): string => $state->getColor())
                    ->formatStateUsing(fn (AccrueRevenueStatus $state): string => $state->getLabel()),
                TextColumn::make('project.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.number')
                    ->label('Project Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company_code')
                    ->label('BU')
                    ->toggleable(),
                TextColumn::make('items.workCompletionReport.number')
                    ->label('BAPP')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('items.revenueType.name')
                    ->label('Revenue Segment')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('work_period')
                    ->label('Work Period')
                    ->date('F Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('accrual_period')
                    ->label('Accrual Period')
                    ->date('F Y')
                    ->sortable(),
                TextColumn::make('total_amount_estimated')
                    ->label('Estimated Revenue')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Estimated')),
                TextColumn::make('total_amount_actual')
                    ->label('Actual Revenue')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Actual'))
                    ->weight('bold'),
            ])
            ->defaultSort('accrual_period', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('downloadSap')
                        ->label('Download SAP Template')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->mountUsing(function (Schema $form, $record) {
                            $missing = app(AccrualMappingService::class)->getMissingMappings($record);
                            $form->fill([
                                'missing_mappings' => $missing,
                            ]);
                        })
                        ->schema(function ($record) {
                            $missing = app(AccrualMappingService::class)->getMissingMappings($record);

                            if (empty($missing)) {
                                return [];
                            }

                            return [
                                Section::make('Missing Account Mappings')
                                    ->description('Beberapa item di dokumen ini belum memiliki pemetaan GL Account. Silakan lengkapi di bawah ini untuk melanjutkan ekspor SAP.')
                                    ->schema([
                                        Repeater::make('missing_mappings')
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        TextInput::make('revenue_type_name')
                                                            ->label('Revenue Segment')
                                                            ->disabled(),
                                                        Select::make('accrual_chart_of_account_id')
                                                            ->label('Accrual GL Account (Debit)')
                                                            ->options(ChartOfAccount::pluck('name', 'id'))
                                                            ->searchable()
                                                            ->required(fn ($get) => $get('missing_accrual'))
                                                            ->hidden(fn ($get) => ! $get('missing_accrual')),
                                                        Select::make('revenue_chart_of_account_id')
                                                            ->label('Revenue GL Account (Credit)')
                                                            ->options(ChartOfAccount::pluck('name', 'id'))
                                                            ->searchable()
                                                            ->required(fn ($get) => $get('missing_revenue'))
                                                            ->hidden(fn ($get) => ! $get('missing_revenue')),
                                                    ]),
                                            ])
                                            ->addable(false)
                                            ->deletable(false)
                                            ->reorderable(false),
                                    ]),
                            ];
                        })
                        ->action(function ($record, array $data) {
                            $mappingService = app(AccrualMappingService::class);

                            // 1. Save missing mappings if provided
                            if (! empty($data['missing_mappings'])) {
                                foreach ($data['missing_mappings'] as $missing) {
                                    if (! empty($missing['accrual_chart_of_account_id'])) {
                                        AccountMapping::updateOrCreate([
                                            'mappable_type' => $missing['mappable_type'],
                                            'mappable_id' => $missing['mappable_id'],
                                            'type' => 'accrual',
                                            'revenue_type_id' => $missing['revenue_type_id'],
                                            'revenue_segment_id' => $missing['revenue_segment_id'],
                                        ], [
                                            'chart_of_account_id' => $missing['accrual_chart_of_account_id'],
                                        ]);
                                    }

                                    if (! empty($missing['revenue_chart_of_account_id'])) {
                                        AccountMapping::updateOrCreate([
                                            'mappable_type' => $missing['mappable_type'],
                                            'mappable_id' => $missing['mappable_id'],
                                            'type' => 'revenue',
                                            'revenue_type_id' => $missing['revenue_type_id'],
                                            'revenue_segment_id' => $missing['revenue_segment_id'],
                                        ], [
                                            'chart_of_account_id' => $missing['revenue_chart_of_account_id'],
                                        ]);
                                    }
                                }
                            }

                            $filename = 'SAP_Accrual_'.$record->number.'_'.now()->format('YmdHis').'.xlsx';

                            return Excel::download(
                                new SapAccrualExport([$record], $mappingService),
                                $filename
                            );
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkAction::make('downloadSapBulk')
                    ->label('Export Selected to SAP')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $mappingService = app(AccrualMappingService::class);
                        $filename = 'SAP_BULK_UPLOAD_'.date('Ymd_His').'.csv';

                        $headers = [
                            'Content-type' => 'text/csv',
                            'Content-Disposition' => "attachment; filename=$filename",
                            'Pragma' => 'no-cache',
                            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                            'Expires' => '0',
                        ];

                        $callback = function () use ($records, $mappingService) {
                            $file = fopen('php://output', 'w');
                            fputcsv($file, ['Company Code', 'Period', 'Currency', 'Reference', 'Doc Type', 'Posting Key', 'GL Account', 'Amount', 'Text']);

                            foreach ($records as $record) {
                                foreach ($record->items as $item) {
                                    $amount = $item->amount_estimated;
                                    $period = $record->accrual_period->format('m/Y');

                                    $accrualAccount = $mappingService->resolveAccount(
                                        'accrual',
                                        $record->projectArea,
                                        $record->customer,
                                        $item->revenue_type_id,
                                        $record->project?->revenue_segment_id
                                    );
                                    $revenueAccount = $mappingService->resolveAccount(
                                        'revenue',
                                        $record->projectArea,
                                        $record->customer,
                                        $item->revenue_type_id,
                                        $record->project?->revenue_segment_id
                                    );

                                    // Row 1: Debit Accrual (40)
                                    fputcsv($file, [
                                        $record->company_code,
                                        $period,
                                        'IDR',
                                        $record->sap_reference ?? $record->number,
                                        'SA',
                                        '40',
                                        $accrualAccount ?? 'MISSING_GL',
                                        $amount,
                                        'Accrual '.($item->revenueType?->name ?? 'Accrual'),
                                    ]);

                                    // Row 2: Credit Revenue (50)
                                    fputcsv($file, [
                                        $record->company_code,
                                        $period,
                                        'IDR',
                                        $record->sap_reference ?? $record->number,
                                        'SA',
                                        '50',
                                        $revenueAccount ?? 'MISSING_GL',
                                        $amount,
                                        'Revenue '.($item->revenueType?->name ?? 'Revenue'),
                                    ]);
                                }
                            }
                            fclose($file);
                        };

                        return response()->stream($callback, 200, $headers);
                    }),
            ])
            ->filters([
                Filter::make('ready_for_sap')
                    ->label('Ready for SAP')
                    ->query(fn ($query) => $query->where('status', AccrueRevenueStatus::Open)->whereNull('sap_reference'))
                    ->indicator('Ready for SAP'),
            ]);
    }
}
