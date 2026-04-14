<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Models\SalesOrderAmendment;

class AmendmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amendment_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reason')
                    ->limit(50),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Amendment Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(function (SalesOrderAmendment $record) {
                        $pdf = Pdf::loadView('crm::pdf.sales-order-amendment', ['record' => $record]);
                        $filename = "soa-{$record->salesOrder->so_number}-rev{$record->amendment_number}";
                        $filename = str_replace(['/', '\\'], '-', $filename);

                        return response()->streamDownload(fn () => print ($pdf->output()), "{$filename}.pdf");
                    }),
            ]);
    }
}
