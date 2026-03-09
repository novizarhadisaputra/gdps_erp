<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Exports\ProfitabilityAnalysisSummaryExport;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Traits\HasProfitabilityAnalysisActions;

class SummaryProfitabilityAnalysis extends ViewRecord
{
    use HasProfitabilityAnalysisActions;
    use InteractsWithParentRecord;

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
                ->icon('heroicon-m-pencil-square')
                ->color('info')
                ->button(),
            Action::make('exportExcel')
                ->label('Excel')
                ->icon('heroicon-o-document-chart-bar')
                ->color('success')
                ->action(function () {
                    $filename = 'profitability_summary_'.($this->record->document_number ?? $this->record->id).'.xlsx';
                    $filename = str_replace(['/', '\\'], '_', $filename);

                    return Excel::download(
                        new ProfitabilityAnalysisSummaryExport($this->record),
                        $filename
                    );
                }),
            Action::make('exportPdf')
                ->label('PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                        'finance::filament.clusters.finance.resources.profitability-analyses.pages.summary',
                        [
                            'record' => $this->record,
                            'isExport' => true,
                            'isPdf' => true,
                        ]
                    )->setPaper('a4', 'portrait');

                    $filename = 'profitability_summary_'.($this->record->document_number ?? $this->record->id).'.pdf';
                    $filename = str_replace(['/', '\\'], '_', $filename);

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename, [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
            ...$this->getProfitabilityAnalysisActions(),
        ];
    }
}
