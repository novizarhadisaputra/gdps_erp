<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Services\AccrualMappingService;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->money('IDR')
                    ),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('exportAllSap')
                    ->label('Export All to SAP')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Table $table) {
                        $records = $table->getQuery()->get();

                        return static::exportToSap($records);
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn ($record) => $record->status === InvoiceStatus::Draft),
                    DeleteAction::make()
                        ->visible(fn ($record) => $record->status === InvoiceStatus::Draft),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('exportSelectedSap')
                        ->label('Export Selected to SAP')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(fn (Collection $records) => static::exportToSap($records)),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function exportToSap(Collection $records)
    {
        $mappingService = app(AccrualMappingService::class);
        $filename = 'SAP_INVOICE_EXPORT_'.date('Ymd_His').'.csv';

        return response()->stream(function () use ($records, $mappingService) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Company Code', 'Period', 'Currency', 'Reference', 'Doc Type', 'Posting Key', 'GL Account', 'Amount', 'Text']);

            foreach ($records as $record) {
                $period = $record->invoice_date->format('m/Y');
                $amount = $record->total_amount;

                // Resolve AR Account
                $arAccount = $mappingService->resolveAccount('receivable', $record->projectArea, $record->customer);
                // Resolve Accrued Revenue Account (for reversal)
                $accruedAccount = $mappingService->resolveAccount('accrual', $record->projectArea, $record->customer);

                // Debit AR (01 or 40 depending on SAP config, usually 01 for customer but let's use 40/50 for GL consistency if using GL mapping)
                fputcsv($file, [$record->company_code ?? 'GDPS', $period, 'IDR', $record->number, 'RV', '01', $arAccount ?? 'MISSING_AR', $amount, 'Invoice '.$record->number]);
                // Credit Accrued Revenue
                fputcsv($file, [$record->company_code ?? 'GDPS', $period, 'IDR', $record->number, 'RV', '50', $accruedAccount ?? 'MISSING_ACCRUAL', $amount, 'Reversal Accrual '.$record->number]);
            }
            fclose($file);
        }, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }
}
