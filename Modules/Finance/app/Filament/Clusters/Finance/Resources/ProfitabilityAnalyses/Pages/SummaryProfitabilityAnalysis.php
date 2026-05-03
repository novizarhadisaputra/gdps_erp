<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Exports\ProfitabilityAnalysisExport;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Traits\HasProfitabilityAnalysisActions;
use Modules\Finance\Models\ProfitabilityAnalysis;

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
            // 1. Workflow Actions (The most important ones)
            ActionGroup::make([
                $this->getSubmitAction(),
                $this->getApproveMarginAction(),
                $this->getApprovePAAction(),
                $this->getGenerateProjectAction(),
            ])
                ->label('Workflow')
                ->icon(Heroicon::OutlinedPlay)
                ->color('primary')
                ->button(),

            // 2. Document Data Actions
            ActionGroup::make($this->getStepActions())
                ->label('Edit Details')
                ->icon(Heroicon::PencilSquare)
                ->color('info')
                ->button(),

            // 3. Output & Export
            ActionGroup::make([
                Action::make('exportExcel')
                    ->label('Excel')
                    ->icon(Heroicon::OutlinedTableCells)
                    ->color('success')
                    ->action(function (ProfitabilityAnalysis $record) {
                        $number = str_replace(['/', '\\'], '-', $record->number ?? 'Draft');
                        $filename = "{$number}.xlsx";

                        return Excel::download(
                            new ProfitabilityAnalysisExport($record),
                            $filename
                        );
                    }),
                Action::make('exportPdf')
                    ->label('PDF')
                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                    ->color('danger')
                    ->action(function (ProfitabilityAnalysis $record) {
                        $pdf = Pdf::loadView(
                            'finance::pdf.profitability-analysis',
                            [
                                'record' => $record,
                                'isExport' => true,
                                'isPdf' => true,
                            ]
                        )->setPaper('a4', 'portrait');

                        $number = str_replace(['/', '\\'], '-', $record->number ?? 'Draft');
                        $filename = "{$number}.pdf";

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename, [
                            'Content-Type' => 'application/pdf',
                        ]);
                    }),
                $this->getDuplicateAction(),
            ])
                ->label('Export & Tools')
                ->icon(Heroicon::ArrowDownTray)
                ->color('success')
                ->button(),

            // 4. Other Options
            ActionGroup::make([
                $this->getRejectAction(),
                $this->getCreateProposalAction(),
                $this->getRegenerateSalesOrderAction(),
            ])
                ->label('Settings')
                ->icon(Heroicon::OutlinedCog6Tooth)
                ->color('gray')
                ->button(),
        ];
    }
}
