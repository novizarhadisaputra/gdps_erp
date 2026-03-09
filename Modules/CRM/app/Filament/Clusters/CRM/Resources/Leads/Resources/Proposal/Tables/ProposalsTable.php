<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Exports\ProposalExport;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Schemas\ProposalForm;
use Modules\CRM\Models\Proposal;

class ProposalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('workScheme.name')
                    ->label('Scheme')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proposal_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('submission_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->label('Customer')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options(ProposalStatus::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('export_pdf')
                        ->label('Export PDF')
                        ->icon('heroicon-o-document-text')
                        ->action(function (Proposal $record) {
                            if ($record->is_manual && $media = $record->getFirstMedia('final_proposal')) {
                                return $media;
                            }

                            $pdf = Pdf::loadView('crm::pdf.proposal', ['record' => $record]);
                            $filename = str_replace(['/', '\\'], '-', $record->proposal_number);

                            return response()->streamDownload(fn () => print ($pdf->output()), "proposal-{$filename}.pdf");
                        }),

                    Action::make('export_excel')
                        ->label('Export Excel')
                        ->icon('heroicon-o-table-cells')
                        ->action(function (Proposal $record) {
                            $filename = str_replace(['/', '\\'], '-', $record->proposal_number);

                            return Excel::download(
                                new ProposalExport($record),
                                "proposal-{$filename}.xlsx"
                            );
                        }),

                    Action::make('export_contract')
                        ->label('Export Contract')
                        ->icon('heroicon-o-document-duplicate')
                        ->visible(fn (Proposal $record) => $record->contracts()->exists())
                        ->action(function (Proposal $record) {
                            $contract = $record->contracts()->latest()->first();
                            $pdf = Pdf::loadView('crm::pdf.contract', ['record' => $contract]);

                            $filename = str_replace(['/', '\\'], '-', $contract->contract_number);

                            return response()->streamDownload(fn () => print ($pdf->output()), "contract-{$filename}.pdf");
                        }),

                    Action::make('export_general_information')
                        ->label('Export General Info')
                        ->icon('heroicon-o-information-circle')
                        ->visible(fn (Proposal $record) => $record->lead?->generalInformations()->exists())
                        ->action(function (Proposal $record) {
                            $gi = $record->lead->generalInformations()->latest()->first();
                            $pdf = Pdf::loadView('crm::pdf.general_information', ['record' => $gi]);

                            return response()->streamDownload(fn () => print ($pdf->output()), "general-information-{$gi->customer->name}.pdf");
                        }),
                ])
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->button(),

                ViewAction::make(),
                EditAction::make()
                    ->schema(fn (Schema $schema) => ProposalForm::configure($schema)),
                DeleteAction::make(),
                EditAction::make()
                    ->schema(fn (Schema $schema) => ProposalForm::configure($schema)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
