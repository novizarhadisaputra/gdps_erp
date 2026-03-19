<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Traits\HasProfitabilityAnalysisActions;

class SummaryProfitabilityAnalysis extends ViewRecord
{
    use HasProfitabilityAnalysisActions;

    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected string $view = 'finance::filament.clusters.finance.resources.profitability-analyses.pages.summary';

    public function getTitle(): string
    {
        return '';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ActionGroup::make($this->getStepActions())
                ->label('Edit Steps')
                ->icon(Heroicon::PencilSquare)
                ->color('info')
                ->button(),
            \Filament\Actions\ActionGroup::make([
                Action::make('exportExcel')
                    ->label('Excel')
                    ->icon(Heroicon::OutlinedTableCells)
                    ->color('success')
                    ->action(function (\Modules\Finance\Models\ProfitabilityAnalysis $record) {
                        $filename = 'profitability_analysis_'.($record->document_number ?? $record->id).'.xlsx';
                        $filename = str_replace(['/', '\\'], '_', $filename);

                        return Excel::download(
                            new \Modules\Finance\Exports\ProfitabilityAnalysisExport($record),
                            $filename
                        );
                    }),
                Action::make('exportPdf')
                    ->label('PDF')
                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                    ->color('danger')
                    ->action(function (\Modules\Finance\Models\ProfitabilityAnalysis $record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                            'finance::pdf.profitability-analysis',
                            [
                                'record' => $record,
                                'isExport' => true,
                                'isPdf' => true,
                            ]
                        )->setPaper('a4', 'portrait');

                        $filename = 'profitability_summary_'.($record->document_number ?? $record->id).'.pdf';
                        $filename = str_replace(['/', '\\'], '_', $filename);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename, [
                            'Content-Type' => 'application/pdf',
                        ]);
                    }),
            ])
                ->label('Export')
                ->icon(Heroicon::ArrowDownTray)
                ->color('success')
                ->button(),
            ...$this->getProfitabilityAnalysisActions(),
        ];
    }
}
