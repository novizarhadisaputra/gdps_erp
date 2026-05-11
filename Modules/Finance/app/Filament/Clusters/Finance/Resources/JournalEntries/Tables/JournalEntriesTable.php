<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Finance\Models\JournalEntry;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Voucher #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'posted' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('reference_type')
                    ->label('Source')
                    ->state(function ($record) {
                        if (! $record->reference_type) {
                            return '-';
                        }
                        $class = class_basename($record->reference_type);

                        return $class;
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('printVoucher')
                        ->label('Print Voucher')
                        ->color('gray')
                        ->icon('heroicon-o-printer')
                        ->action(function (JournalEntry $record) {
                            $logo = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/branding/header_left.png')));

                            $sourceType = '-';
                            if ($record->reference_type) {
                                $sourceType = class_basename($record->reference_type);
                            }

                            $pdf = Pdf::loadView('finance::pdf.journal-voucher', [
                                'record' => $record,
                                'logo' => $logo,
                                'sourceType' => $sourceType,
                            ]);

                            $name = str_replace(['/', '\\'], '-', $record->number);
                            $fileName = "Voucher-{$name}.pdf";

                            return response()->streamDownload(
                                fn () => print ($pdf->output()),
                                $fileName
                            );
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
